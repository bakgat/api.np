<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 7/11/16
 * Time: 13:21
 */

namespace App\Repositories\Evaluation;


use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\Goal;
use App\Domain\Model\Education\Major;
use App\Domain\Model\Evaluation\Exceptions\IacNotFoundException;
use App\Domain\Model\Evaluation\IAC;
use App\Domain\Model\Evaluation\IACGoal;
use App\Domain\Model\Evaluation\IACRepository;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Reporting\FlatIAC;
use App\Domain\NtUid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;

class IACDoctrineRepository implements IACRepository
{
    /** @var EntityManager */
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function allGoals()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('m, b, g')
            ->from(Major::class, 'm')
            ->join('m.branches', 'b')
            ->join('b.goals', 'g');

        return $qb->getQuery()->getResult();
    }

    public function allGoalsForMajor(Major $major)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('m, b, g')
            ->from(Major::class, 'm')
            ->join('m.branches', 'b')
            ->join('b.goals', 'g')
            ->where('m.id=:major')
            ->setParameter('major', $major->getId());

        return $qb->getQuery()->getResult();
    }


    public function allGoalsForBranch(Branch $branch)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('b, g')
            ->from(Branch::class, 'b')
            ->join('b.goals', 'g')
            ->where('b.id=:branch')
            ->setParameter('branch', $branch->getId());

        return $qb->getQuery()->getResult();
    }



    /**
     * @return ArrayCollection
     */
    public function getIacs()
    {
        $sql = "SELECT ig.id as ig_id, ig.achieved as ig_achieved, ig.practice as ig_practice, ig.comment as ig_comment, ig.date as ig_date,
                    iac.id as iac_id, iac.start as iac_start, iac.end as iac_end,
                    s.id as s_id, s.first_name as s_first_name, s.last_name as s_last_name,
                    g.id as g_id, g.text as g_text,
                    b.id as b_id, b.name as b_name,
                    m.id as m_id, m.name as m_name
                    FROM iacs iac
                    INNER JOIN iac_goals ig ON ig.iac_id = iac.id
                    INNER JOIN goals g ON ig.goal_id = g.id
                    INNER JOIN branches b ON b.id = g.branch_id
                    INNER JOIN majors m ON m.id = b.major_id
                    INNER JOIN students s ON s.id = iac.student_id";
        return $this->getIac($sql);
    }

    public function iacForStudent(Student $student)
    {/*
        $sql = "SELECT ig.id as ig_id, ig.achieved as ig_achieved, ig.practice as ig_practice, ig.comment as ig_comment, ig.date as ig_date,
                    iac.id as iac_id, iac.start as iac_start, iac.end as iac_end,
                    s.id as s_id, s.first_name as s_first_name, s.last_name as s_last_name,
                    g.id as g_id, g.text as g_text,
                    b.id as b_id, b.name as b_name,
                    m.id as m_id, m.name as m_name
                    FROM iacs iac
                    INNER JOIN iac_goals ig ON ig.iac_id = iac.id
                    INNER JOIN goals g ON ig.goal_id = g.id
                    INNER JOIN branches b ON b.id = g.branch_id
                    INNER JOIN majors m ON m.id = b.major_id
                    INNER JOIN students s ON s.id = iac.student_id
                    WHERE s.id = '" . $student->getId() . "'";
*/
        $qb = $this->em->createQueryBuilder();
        $qb->select('iac, ig, m, b, g')
            ->from(IAC::class, 'iac')
            ->join('iac.branch', 'b')
            ->join('b.major', 'm')
            ->join('iac.iacGoals', 'ig')
            ->join('ig.goal', 'g')
            ->where('iac.student=:student')
            ->setParameter('student', $student->getId());
        return $qb->getQuery()->getResult();
        //return $this->getIac($sql);
    }

    /**
     * @param $sql
     * @return array
     */
    private function getIac($sql)
    {
        $rsm = new ResultSetMapping;

        $rsm->addEntityResult(FlatIAC::class, 'i')
            ->addFieldResult('i', 'ig_id', 'igId')
            ->addFieldResult('i', 'iac_id', 'iacId')
            ->addFieldResult('i', 'iac_start', 'iacStart')
            ->addFieldResult('i', 'iac_end', 'iacEnd')
            ->addFieldResult('i', 's_id', 'sId')
            ->addFieldResult('i', 's_first_name', 'sFirstName')
            ->addFieldResult('i', 's_last_name', 'sLastName')
            ->addFieldResult('i', 'g_id', 'gId')
            ->addFieldResult('i', 'g_text', 'gText')
            ->addFieldResult('i', 'ig_achieved', 'igAchieved')
            ->addFieldResult('i', 'ig_practice', 'igPractice')
            ->addFieldResult('i', 'ig_comment', 'igComment')
            ->addFieldResult('i', 'ig_date', 'igDate')
            ->addFieldResult('i', 'b_id', 'bId')
            ->addFieldResult('i', 'b_name', 'bName')
            ->addFieldResult('i', 'm_id', 'mId')
            ->addFieldResult('i', 'm_name', 'mName');

        $query = $this->em->createNativeQuery($sql, $rsm);
        $result = $query->getArrayResult();
        return $result;
    }


    /**
     * @param NtUid $id
     * @return Goal
     */
    public function getGoal(NtUid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('g')
            ->from(Goal::class, 'g')
            ->where('g.id=:id')
            ->setParameter('id', $id);
        return $qb->getQuery()->getOneOrNullResult();
        
    }

    public function insert(IAC $iac)
    {
        $this->em->persist($iac);
        $this->em->flush();
    }

    public function get(NtUid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('iac', 'ig', 'g')
            ->from(IAC::class, 'iac')
            ->join('iac.iacGoals', 'ig')
            ->join('ig.goal', 'g')
            ->where('iac.id=:id')
            ->setParameter('id',$id);

        $iac = $qb->getQuery()->getOneOrNullResult();
        
        if($iac == null) {
            throw new IacNotFoundException($id);
        }
        return $iac;
    }

    public function update(IAC $iac)
    {
        $this->em->persist($iac);

        $this->em->flush();
        return 1;
    }
}