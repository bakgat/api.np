<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 23/06/16
 * Time: 15:28
 */

namespace App\Repositories\Identity;


use App\Domain\Model\Identity\Exceptions\NonUniqueGroupNameException;
use App\Domain\Model\Identity\Exceptions\GroupNotFoundException;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\GroupRepository;
use App\Domain\Model\Identity\StaffInGroup;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Identity\StudentInGroup;
use App\Domain\NtUid;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Illuminate\Support\Facades\Cache;

class GroupDoctrineRepository implements GroupRepository
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
     * Gets all the active groups
     *
     * @return ArrayCollection|Group[]
     */
    public function allActive()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('g')
            ->from(Group::class, 'g')
            ->join('g.studentInGroups', 'sig')
            ->where('sig.dateRange.start<=?1')
            ->andWhere('sig.dateRange.end>=?1')
            ->setParameter(1, new DateTime);

        return $qb->getQuery()->getResult();
    }

    /**
     * Finds a group by its id, if not returns null.
     *
     * @param NtUid $id
     * @return Group|null
     */
    public function find(NtUid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('g')
            ->from(Group::class, 'g')
            ->where('g.id=?1')
            ->setParameter(1, $id);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Saves a new group.
     *
     * @param Group $group
     * @return NtUid
     * @throws NonUniqueGroupNameException
     */
    public function insert(Group $group)
    {
        if (in_array($group->getName(), $this->getNames())) {
            throw new NonUniqueGroupNameException($group->getName());
        }

        $this->em->persist($group);
        $this->em->flush();

        Cache::forget('group_names');
        $this->getNames(); //reconfigure cache

        return $group->getId();
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
    public function delete(NtUid $id)
    {
        $group = $this->get($id);
        $this->em->remove($group);
        $this->em->flush();
        Cache::forget('group_names');
        $this->getNames();
        return 1;
    }

    /**
     * Gets an existing group by its id.
     *
     * @param NtUid $id
     * @return Group
     * @throws GroupNotFoundException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function get(NtUid $id)
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
     * Gets all the active students in a group.
     *
     * @param NtUid $id
     * @return ArrayCollection|Student[]
     */
    public function allActiveStudents(NtUid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('s')
            ->from(Student::class, 's')
            ->join('s.studentInGroups', 'sig')
            ->where('sig.group=?1')
            ->andWhere('sig.dateRange.start<=?2')
            ->andWhere('sig.dateRange.end>=?2')
            ->setParameter(1, $id)
            ->setParameter(2, new DateTime);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param NtUid $id
     * @return StaffInGroup
     */
    public function getStaffGroup(NtUid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('sg, g')
            ->from(StaffInGroup::class, 'sg')
            ->join('sg.group', 'g')
            ->where('sg.id=:id')
            ->setParameter('id', $id);

        // TODO: Throw error because GET must return existing or throw
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param StaffInGroup $staffGroup
     * @return int Number of affected rows
     */
    public function updateStaffGroup(StaffInGroup $staffGroup)
    {
        $this->em->persist($staffGroup);
        $this->em->flush();
        return 1;
    }

    /**
     * @param NtUid $id
     * @return StudentInGroup
     */
    public function getStudentGroup(NtUid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('sig, g')
            ->from(StudentInGroup::class, 'sig')
            ->join('sig.group', 'g')
            ->where('sig.id=:id')
            ->setParameter('id', $id);

        // TODO: Throw error because GET must return existing or throw
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $studentGroup
     * @return StudentInGroup
     */
    public function updateStudentGroup($studentGroup)
    {
        $this->em->persist($studentGroup);
        $this->em->flush();
        return 1;
    }
}