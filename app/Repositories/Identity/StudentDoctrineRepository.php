<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 22/06/16
 * Time: 16:17
 */

namespace App\Repositories\Identity;


use App\Domain\Model\Evaluation\RedicodiForStudent;
use App\Domain\Model\Identity\ArrayCollection;
use App\Domain\Model\Identity\Exceptions\StudentNotFoundException;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Identity\StudentInGroup;
use App\Domain\Model\Identity\StudentRepository;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Webpatser\Uuid\Uuid;

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
            ->join('s.studentInGroups', 'sig')
            ->join('sig.group', 'g')
            ->orderBy('s.lastName');
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

        $qb->select('s')
            ->from(Student::class, 's')
            ->join('s.studentInGroups', 'sig')
            ->where($qb->expr()->andX(
                $qb->expr()->lte('sig.dateRange.start', '?1'),
                $qb->expr()->gte('sig.dateRange.end', '?1'),
                $qb->expr()->eq('sig.group', '?2')
            ))
            ->setParameter(1, $date)
            ->setParameter(2, $group->getId())
            ->orderBy('s.lastName');

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
     * @param Uuid $id
     * @return Student|null
     */
    public function find(Uuid $id)
    {
        $query = $this->em->createQuery('SELECT s FROM ' . Student::class . ' s WHERE s.id=?1')
            ->setParameter(1, $id);
        return $query->getOneOrNullResult();
    }

    /**
     * Gets an existing student by its id
     *
     * @param Uuid $id
     * @return Student
     * @throws StudentNotFoundException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function get(Uuid $id)
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
     * @return Uuid
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
    public function delete(Uuid $id)
    {
        $student = $this->get($id);
        $this->em->remove($student);
        $this->em->flush();
        return 1;
    }

    /**
     * Gets all groups where a student was member of.
     *
     * @param Uuid $id
     * @return array
     */
    public function allGroups(Uuid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('sig, g')
            ->from(StudentInGroup::class, 'sig')
            ->join('sig.group', 'g')
            ->orderBy('sig.dateRange.start', 'DESC')
            ->where('sig.student=?1')
            ->setParameter(1, $id);

        return $qb->getQuery()->getResult();
    }


    /* ***************************************************
     * REDICODI
     * **************************************************/

    /**
     * Gets all 'redicodi' applicable for a given student.
     *
     * @param Uuid $id
     * @return RedicodiForStudent[]
     */
    public function allRedicodi(Uuid $id)
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
    }


    /**
     * @param Uuid $id
     * @return RedicodiForStudent
     */
    public function getStudentRedicodi(Uuid $id)
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


}