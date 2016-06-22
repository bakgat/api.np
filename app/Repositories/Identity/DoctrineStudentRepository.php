<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 22/06/16
 * Time: 16:17
 */

namespace App\Repositories\Identity;


use App\Domain\Model\Identity\ArrayCollection;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Identity\StudentRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Webpatser\Uuid\Uuid;

class DoctrineStudentRepository implements StudentRepository
{
    /** @var EntityManager */
    protected $em;


    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Gets all thetive students.
     *
     * @return ArrayCollection|Student[]
     */
    public function all()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('s')
            ->from(Student::class, 's');

        return $qb->getQuery()->getResult();
    }

    /**
     * Finds a student by its id, if not returns null
     *
     * @param $id
     * @return Student|null
     */
    public function find($id)
    {
        $query = $this->em->createQuery('SELECT s FROM ' . Student::class . ' s WHERE s.id=?1')
            ->setParameter(1, $id);
        return $query->getOneOrNullResult();
    }

    /**
     * Gets an existing student by its id
     *
     * @param $id
     * @return Student
     * @throws EntityNotFoundException
     */
    public function get($id)
    {
        $query = $this->em->createQuery('SELECT s FROM ' . Student::class . ' s WHERE s.id=?1')
            ->setParameter(1, $id);

        $student = $query->getOneOrNullResult();

        if ($student == null) {
            throw new EntityNotFoundException($id);
        }

        return $student;
    }
}