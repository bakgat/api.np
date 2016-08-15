<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 15/08/16
 * Time: 09:44
 */

namespace App\Repositories\Identity;


use App\Domain\Model\Identity\Collection;
use App\Domain\Model\Identity\DateTime;
use App\Domain\Model\Identity\Exceptions\StaffNotFoundException;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Role;
use App\Domain\Model\Identity\Staff;
use App\Domain\Model\Identity\StaffRepository;
use App\Domain\Model\Identity\Student;
use App\Domain\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

class StaffDoctrineRepository implements StaffRepository
{
    /** @var  EntityManager */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get all staff members.
     *
     * @return ArrayCollection
     */
    public function all()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('s, sig, g, sr, r')
            ->from(Staff::class, 's')
            ->leftJoin('s.staffInGroups', 'sig')
            ->leftJoin('sig.group', 'g')
            ->leftJoin('s.staffRoles', 'sr')
            ->leftJoin('sr.role', 'r')
            ->orderBy('s.lastName');
        return $qb->getQuery()->getResult();
    }

    /**
     * Get all active staff members in a role.
     *
     * @param Role $group
     * @param DateTime|null $date
     * @return ArrayCollection
     */
    public function allActiveInRole(Role $group, $date = null)
    {
        // TODO: Implement allActiveInRole() method.
    }

    /**
     * Gets all the active staff members in a group.
     *
     * @param Group $group
     * @param DateTime|null $date
     * @return Collection
     */
    public function allActiveInGroup(Group $group, $date = null)
    {
        // TODO: Implement allActiveInGroup() method.
    }

    /**
     * Finds a staff member by its id, if not returns null.
     *
     * @param Uuid $id
     * @return Staff|null
     */
    public function find(Uuid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('s, sig, g, sr, r')
            ->from(Staff::class, 's')
            ->leftJoin('s.staffInGroups', 'sig')
            ->leftJoin('sig.group', 'g')
            ->leftJoin('s.staffRoles', 'sr')
            ->leftJoin('sr.role', 'r')
            ->where('s.id=?1')
            ->setParameter(1, $id);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Finds a staff member by its email address, if not returns null.
     *
     * @param $email
     * @return Staff|null
     */
    public function findByEmail($email)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('s, sig, g, sr, r')
            ->from(Staff::class, 's')
            ->leftJoin('s.staffInGroups', 'sig')
            ->leftJoin('sig.group', 'g')
            ->leftJoin('s.staffRoles', 'sr')
            ->leftJoin('sr.role', 'r')
            ->where('s.email=?1')
            ->setParameter(1, $email);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Gets an existing staff member by its id.
     *
     * @param Uuid $id
     * @return Student
     */
    public function get(Uuid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('s, sig, g, sr, r')
            ->from(Staff::class, 's')
            ->leftJoin('s.staffInGroups', 'sig')
            ->leftJoin('sig.group', 'g')
            ->leftJoin('s.staffRoles', 'sr')
            ->leftJoin('sr.role', 'r')
            ->where('s.id=?1')
            ->setParameter(1, $id);
        $staff = $qb->getQuery()->getOneOrNullResult();

        if($staff == null) {
            throw new StaffNotFoundException($id);
        }

        return $staff;
    }

    /**
     * Saves a new staff member.
     *
     * @param Staff $staff
     * @return Uuid
     */
    public function insert(Staff $staff)
    {
        // TODO: Implement insert() method.
    }

    /**
     * Saves an existing staff member.
     *
     * @param Staff $staff
     * @return int Number of affected rows.
     */
    public function update(Staff $staff)
    {
        // TODO: Implement update() method.
    }

    /**
     * Deletes an existing staff member.
     *
     * @param Uuid $id
     * @return int Number of affected rows.
     */
    public function delete(Uuid $id)
    {
        // TODO: Implement delete() method.
    }


}