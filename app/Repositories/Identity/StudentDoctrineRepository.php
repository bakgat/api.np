<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 22/06/16
 * Time: 16:17
 */

namespace App\Repositories\Identity;


use App\Domain\Model\Evaluation\RedicodiForStudent;
use App\Domain\Model\Identity\Exceptions\StudentNotFoundException;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Identity\StudentRepository;
use App\Domain\NtUid;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;

class StudentDoctrineRepository implements StudentRepository
{
    /** @var EntityManager */
    protected $em;


    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Gets all the students.
     *
     * @return ArrayCollection|Student[]
     */
    public function all()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('s, sig, g')
            ->from(Student::class, 's')
            ->join('s.studentInGroups', 'sig', Join::WITH,
                $qb->expr()->andX(
                    $qb->expr()->lte('sig.dateRange.start', '?1'),
                    $qb->expr()->gte('sig.dateRange.end', '?1')
                ))
            ->join('sig.group', 'g')
            ->orderBy('sig.number')
            ->setParameter(1, new DateTime);
        return $qb->getQuery()->getResult();
    }

    /**
     * Gets all the active students in a group.
     *
     * @param Group $group
     * @param DateTime|null $date
     * @return Collection
     */
    public function allActiveInGroup(Group $group, $date = null)
    {
        if ($date == null) {
            $date = new DateTime;
        }

        $qb = $this->em->createQueryBuilder();

        $qb->select('s, sig')
            ->from(Student::class, 's')
            ->join('s.studentInGroups', 'sig')
            ->where($qb->expr()->andX(
                $qb->expr()->lte('sig.dateRange.start', '?1'),
                $qb->expr()->gte('sig.dateRange.end', '?1'),
                $qb->expr()->eq('sig.group', '?2'),
                $qb->expr()->gt('sig.number', '?3')
            ))
            ->setParameter(1, $date)
            ->setParameter(2, $group->getId())
            ->setParameter(3, 0)
            ->orderBy('sig.number');

        return $qb->getQuery()->getResult();
    }

    /**
     * Gets a list of id/given field for all students.
     * ie generate a list of id and email-addresses to check client-side uniqueness
     *
     * @param $field
     * @return ArrayCollection|Student[]
     */
    public function flat($field)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('s.id, s.' . $field)
            ->from(Student::class, 's')
            ->orderBy('s.' . $field);
        return $qb->getQuery()->getResult();
    }

    /**
     * Finds a student by its id, if not returns null
     *
     * @param NtUid $id
     * @return Student|null
     */
    public function find(NtUid $id)
    {
        $query = $this->em->createQuery('SELECT s FROM ' . Student::class . ' s WHERE s.id=?1')
            ->setParameter(1, $id);
        return $query->getOneOrNullResult();
    }

    /**
     * Gets an existing student by its id
     *
     * @param NtUid $id
     * @return Student
     * @throws StudentNotFoundException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function get(NtUid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('s')
            ->from(Student::class, 's')
            ->where('s.id=?1')
            ->setParameter(1, $id);

        $student = $qb->getQuery()->getOneOrNullResult();

        if ($student == null) {
            throw new StudentNotFoundException($id);
        }

        return $student;
    }


    /**
     * Saves a new student.
     *
     * @param Student $student
     * @return NtUid
     */
    public function insert(Student $student)
    {
        $this->em->persist($student);
        $this->em->flush();
        return $student->getId();
    }

    /**
     * Saves an existing student.
     *
     * @param Student $student
     * @return int Number of affected rows
     */
    public function update(Student $student)
    {
        $this->em->persist($student);
        $this->em->flush();
        return 1;
    }

    /**
     * Deletes an existing student.
     *
     * @param $id
     * @return int Number of affected rows.
     * @throws StudentNotFoundException
     */
    public function delete(NtUid $id)
    {
        $student = $this->get($id);
        $this->em->remove($student);
        $this->em->flush();
        return 1;
    }

    /**
     * Gets all groups where a student was member of.
     *
     * @param NtUid $id
     * @return array
     */

    // TODO: unused

    /*
    public function allGroups(NtUid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('sig, g')
            ->from(StudentInGroup::class, 'sig')
            ->join('sig.group', 'g')
            ->orderBy('sig.dateRange.start', 'DESC')
            ->where('sig.student=?1')
            ->setParameter(1, $id);

        return $qb->getQuery()->getResult();
    }*/


    /* ***************************************************
     * REDICODI
     * **************************************************/

    // TODO Unused


    /**
     * Gets all 'redicodi' applicable for a given student.
     *
     * @param NtUid $id
     * @return RedicodiForStudent[]
     */
    /*public function allRedicodi(NtUid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('rfs')
            ->from(RedicodiForStudent::class, 'rfs')
            ->join('rfs.student', 's')
            ->join('rfs.branch', 'b')
            ->orderBy('rfs.dateRange.start', 'DESC')
            ->where('rfs.student=?1')
            ->setParameter(1, $id);

        return $qb->getQuery()->getResult();
    }*/


    /**
     * @param NtUid $id
     * @return RedicodiForStudent
     */
    public function getStudentRedicodi(NtUid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('rfs')
            ->from(RedicodiForStudent::class, 'rfs')
            ->where('rfs.id=:id')
            ->setParameter('id', $id);

        //TODO: throw error because of get function
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     *
     * @param RedicodiForStudent $studentRedicodi
     * @return int
     */
    public function updateRedicodi(RedicodiForStudent $studentRedicodi)
    {
        $this->em->persist($studentRedicodi);
        $this->em->flush();
        return 1;
    }


    /**
     * @return int
     */
    public function count()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('count(s.id)')
            ->from(Student::class, 's');
        return $qb->getQuery()->getSingleScalarResult();
    }
}