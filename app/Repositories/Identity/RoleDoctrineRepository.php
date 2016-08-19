<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 18/08/16
 * Time: 19:13
 */

namespace App\Repositories\Identity;


use App\Domain\Model\Identity\Exceptions\RoleNotFoundException;
use App\Domain\Model\Identity\Role;
use App\Domain\Model\Identity\RoleRepository;
use App\Domain\Model\Identity\StaffRole;
use App\Domain\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

class RoleDoctrineRepository implements RoleRepository
{
    /** @var EntityManager */
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Gets all the roles available.
     *
     * @return ArrayCollection
     */
    public function all()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('r')
            ->from(Role::class, 'r');

        return $qb->getQuery()->getResult();
    }

    /**
     * Gets an existing role by its id.
     *
     * @param Uuid $id
     * @return Role
     * @throws RoleNotFoundException
     */
    public function get(Uuid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('r')
            ->from(Role::class, 'r')
            ->where('r.id=:id')
            ->setParameter('id', $id);

        $role = $qb->getQuery()->getOneOrNullResult();

        if($role == null) {
            throw new RoleNotFoundException($id);
        }

        return $role;
    }

    public function getStaffRole(Uuid $id) {
        $qb = $this->em->createQueryBuilder();
        $qb->select('sr, r')
            ->from(StaffRole::class, 'sr')
            ->join('sr.role', 'r')
            ->where('sr.id=:id')
            ->setParameter('id', $id);

        //TODO : failing get should throw error
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param StaffRole $staffRole
     * @return int Number of rows affected
     */
    public function updateStaffRole(StaffRole $staffRole)
    {
        $this->em->persist($staffRole);
        $this->em->flush();
        return 1;
    }
}