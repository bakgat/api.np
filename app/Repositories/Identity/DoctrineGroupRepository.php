<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 23/06/16
 * Time: 15:28
 */

namespace App\Repositories\Identity;


use App\Domain\Model\Identity\ArrayCollection;
use App\Domain\Model\Identity\Exceptions\NonUniqueGroupNameException;
use App\Domain\Model\Identity\Exceptions\GroupNotFoundException;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\GroupRepository;
use Doctrine\ORM\EntityManager;
use Illuminate\Support\Facades\Cache;
use Webpatser\Uuid\Uuid;

class DoctrineGroupRepository implements GroupRepository
{
    /** @var EntityManager */
    protected $em;


    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Gets all the groups.
     *
     * @return ArrayCollection|Group[]
     */
    public function all()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('g')
            ->from(Group::class, 'g');

        return $qb->getQuery()->getResult();
    }

    /**
     * Finds a group by its id, if not returns null.
     *
     * @param $id
     * @return Group|null
     */
    public function find($id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('g')
            ->from(Group::class, 'g')
            ->where('g.id=?1')
            ->setParameter(1, $id);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Gets an existing group by its id.
     *
     * @param Uuid $id
     * @return Group
     * @throws GroupNotFoundException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function get(Uuid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('g')
            ->from(Group::class, 'g')
            ->where('g.id=?1')
            ->setParameter(1, $id);

        $group = $qb->getQuery()->getOneOrNullResult();

        if ($group == null) {
            throw new GroupNotFoundException($id);
        }

        return $group;
    }

    /**
     * Saves a new group.
     *
     * @param Group $group
     * @return Uuid
     * @throws NonUniqueGroupNameException
     */
    public function insert(Group $group)
    {
        if(in_array( $group->getName(), $this->getNames())) {
            throw new NonUniqueGroupNameException($group->getName());
        }

        $this->em->persist($group);
        $this->em->flush();


        $this->getNames(); //reconfigure cache

        return $group->getId();
    }

    /**
     * Saves an existing group.
     *
     * @param Group $group
     * @return int Number of affected rows.
     */
    public function update(Group $group)
    {
        $this->em->persist($group);
        $this->em->flush();
        Cache::forget('group_names');
        $this->getNames();
        return 1;
    }

    /**
     * Deletes an existing group.
     *
     * @param $id
     * @return int Number of affected rows.
     * @
     */
    public function delete(Uuid $id)
    {
        $group = $this->get($id);
        $this->em->remove($group);
        $this->em->flush();
        Cache::forget('group_names');
        $this->getNames();
        return 1;
    }

    /**
     * Get all the names of the groups.
     * Internal function with cache
     *
     * @return array
     */
    private function getNames()
    {
        if (!Cache::has('group_names')) {
            $qb = $this->em->createQueryBuilder();
            $qb->select('g.name')
                ->from(Group::class, 'g');

            $result = $qb->getQuery()->getScalarResult();
            Cache::forever('group_names', array_map('current', $result));
        }

        return Cache::get('group_names');
    }
}