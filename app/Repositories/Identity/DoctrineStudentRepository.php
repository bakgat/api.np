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
}