<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 23/06/16
 * Time: 15:28
 */

namespace App\Repositories\Identity;


use App\Domain\Model\Identity\ArrayCollection;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\GroupRepository;
use Doctrine\ORM\EntityManager;
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
     */
    public function get(Uuid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('g')
            ->from(Group::class, 'g')
            ->where('g.id=?1')
            ->setParameter(1, $id);
        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Saves a new group.
     *
     * @param Group $group
     * @return Uuid
     */
    public function insert(Group $group)
    {
        // TODO: Implement insert() method.
    }

    /**
     * Saves an existing group.
     *
     * @param Group $group
     * @return int Number of affected rows.
     */
    public function update(Group $group)
    {
        // TODO: Implement update() method.
    }

    /**
     * Deletes an existing group.
     *
     * @param $id
     * @return int Number of affected rows.
     */
    public function delete(Uuid $id)
    {
        // TODO: Implement delete() method.
    }
}