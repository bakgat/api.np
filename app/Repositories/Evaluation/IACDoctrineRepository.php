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
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Reporting\FlatIAC;
use App\Domain\Model\Time\DateRange;
use App\Domain\NtUid;
use DateTime;
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
            ->join('b.goals', 'g')
            ->orderBy('m.order, b.order, g.order');

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
            ->orderBy('b.order, g.order')
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
            ->orderBy('g.order')
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
                    INNER JOIN students s ON s.id = iac.student_id
                    INNER JOIN student_in_groups sig ON sig.student_id = s.id
                    ORDER BY sig.number, m.order, b.order, g.order";
        return $this->getIac($sql);
    }

    public function getIacForGroup(Group $group, $range)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('iac, ig, b, m, g, s')
            ->from(IAC::class, 'iac')
            ->join('iac.iacGoals', 'ig')
            ->join('ig.goal', 'g')
            ->join('iac.student', 's')
            ->join('iac.branch', 'b')
            ->join('b.major', 'm')
            ->join('s.studentInGroups', 'sig')
            ->where($qb->expr()->andX(
                $qb->expr()->lte('iac.dateRange.start', '?1'),
                $qb->expr()->gte('iac.dateRange.end', '?1'),
                $qb->expr()->eq('sig.group', '?2')
            ))
            ->setParameter(1, new DateTime)
            ->setParameter(2, $group->getId())
            ->orderBy('sig.number, m.order, b.order, g.order');
        return $qb->getQuery()->getResult();
    }

    /**
     * @param $studentId
     * @param $infinite
     * @return IAC
     */
    public function getIacForStudent($studentId, $infinite)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('iac, ig, b, m, g, s')
            ->from(IAC::class, 'iac')
            ->join('iac.iacGoals', 'ig')
            ->join('ig.goal', 'g')
            ->join('iac.student', 's')
            ->join('iac.branch', 'b')
            ->join('b.major', 'm')
            ->where($qb->expr()->andX(
                $qb->expr()->lte('iac.dateRange.start', '?1'),
                $qb->expr()->gte('iac.dateRange.end', '?1'),
                $qb->expr()->eq('iac.student', '?2')
            ))
            ->orderBy('m.order, b.order, g.order')
            ->setParameter(1, new DateTime)
            ->setParameter(2, $studentId);
        return $qb->getQuery()->getResult();
    }

    /**
     * @param $group
     * @return ArrayCollection
     */
    public function getFlatIacForGroup($groupId, $range)
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
                    INNER JOIN students s ON s.id = iac.student_id
                    INNER JOIN student_in_groups sig ON sig.student_id = s.id
                    WHERE sig.group_id = '{$groupId}'
                    ORDER BY sig.number, m.order, b.order, g.order";

        return $this->getIac($sql);
    }

    public function getFlatIacForStudent($studentId, $range)
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
                    INNER JOIN students s ON s.id = iac.student_id
                    WHERE s.id = '{$studentId}'
                    ORDER BY m.order, b.order, g.order";

        return $this->getIac($sql);
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
            ->orderBy('g.order')
            ->setParameter('id', $id);

        $iac = $qb->getQuery()->getOneOrNullResult();

        if ($iac == null) {
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

    /**
     * @param $iacId
     * @return mixed
     */
    public function remove(IAC $iac)
    {
        $this->em->remove($iac);
        $this->em->flush();
    }


    /* ***************************************************
     * PRIVATE
     * **************************************************/
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


}