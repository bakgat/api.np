<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 6/08/16
 * Time: 23:02
 */

namespace App\Repositories\Evaluation;

use App\Domain\Model\Education\Major;
use App\Domain\Model\Evaluation\Evaluation;
use App\Domain\Model\Evaluation\EvaluationRepository;
use App\Domain\Model\Evaluation\Exceptions\EvaluationNotFoundException;
use App\Domain\Model\Evaluation\PointResult;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Reporting\FlatReport;
use App\Domain\Model\Time\DateRange;
use App\Domain\NtUid;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;

class EvaluationDoctrineRepository implements EvaluationRepository
{
    /** @var EntityManager */
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function allEvaluationsForGroup(Group $group, DateTime $start, DateTime $end)
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('e, bfg, b, m, pr, fr')
            ->from(Evaluation::class, 'e')
            ->join('e.branchForGroup', 'bfg')
            ->join('bfg.branch', 'b')
            ->join('b.major', 'm')
            ->leftJoin('e.pointResults', 'pr')
            ->leftJoin('e.feedbackResults', 'fr')
            ->where('bfg.group= :groupId')
            ->andWhere('e.date >= :start')
            ->andWhere('e.date <= :end')
            ->setParameter('groupId', $group->getId())
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    public function get(NtUid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('e, bfg, b, m, pr, s, fr')
            ->from(Evaluation::class, 'e')
            ->join('e.branchForGroup', 'bfg')
            ->join('bfg.branch', 'b')
            ->join('b.major', 'm')
            ->leftJoin('e.pointResults', 'pr')
            ->leftJoin('e.feedbackResults', 'fr')
            ->leftJoin('pr.student', 's')
            ->leftJoin('s.studentInGroups', 'sig')
            ->where('e.id=?1')
            ->setParameter(1, $id)
            ->orderBy('sig.number');

        $evaluation = $qb->getQuery()->getOneOrNullResult();
        if ($evaluation == null) {
            throw new EvaluationNotFoundException($id);
        }
        return $evaluation;
    }

    public function insert(Evaluation $evaluation)
    {
        $this->em->persist($evaluation);
        $this->em->flush();
        return $evaluation->getId();
    }

    public function update(Evaluation $evaluation)
    {
        $this->em->persist($evaluation);
        $this->em->flush();
        return 1;
    }


    /* ***************************************************
     * REPORTING
     * **************************************************/
    public function getSummary(DateRange $range)
    {
        //TODO: now for group relation should be requested range !!

        $sql = "SELECT s.id as s_id, s.first_name as first_name, s.last_name as last_name,
              pr.id as pr_id, pr.p_raw as pr_perm, pr.e_raw as pr_end, pr.total as pr_total, 
              pr.max as pr_max, pr.redicodi as pr_redicodi, pr.evaluation_count as pr_evcount,
              gr.id as gr_id, 
              gr.start as start, gr.end as end,
              m.id as m_id, m.name as m_name, 
              b.id as b_id, b.name as b_name, bfg.id as bfg_id,
              g.id as g_id, g.name as g_name,
              st.first_name as st_first_name, st.last_name as st_last_name
              FROM rr pr
              INNER JOIN students s ON pr.student_id = s.id
              INNER JOIN student_in_groups sig ON s.id = sig.student_id
              INNER JOIN branch_for_groups bfg ON bfg.id = pr.branch_for_group_id
              INNER JOIN groups g ON g.id = bfg.group_id
              INNER JOIN staff_in_groups stig ON stig.group_id = g.id
              INNER JOIN staff st ON st.id = stig.staff_id
              INNER JOIN branches b ON b.id = bfg.branch_id
              INNER JOIN majors m ON m.id = b.major_id
              INNER JOIN graph_ranges gr ON gr.id = pr.graph_range_id
              WHERE gr.end>='" . $range->getEnd()->format('Y-m-d') . "'
                AND stig.type='X'";

        return $this->getReport($sql);
    }

    /**
     * @param $studentId
     * @param $range
     * @return ArrayCollection
     */
    public function getReportsForStudent($studentId, $range)
    {
        return $this->getReportsForStudents([$studentId], $range);
    }

    public function getReportsForStudents($studentIds, DateRange $range)
    {
        $sql = "SELECT s.id as s_id, s.first_name as first_name, s.last_name as last_name,
              pr.id as pr_id, pr.p_raw as pr_perm, pr.e_raw as pr_end, pr.total as pr_total, 
              pr.max as pr_max, pr.redicodi as pr_redicodi, pr.evaluation_count as pr_evcount,
              gr.id as gr_id, 
              gr.start as start, gr.end as end,
              m.id as m_id, m.name as m_name, 
              b.id as b_id, b.name as b_name, bfg.id as bfg_id,
              g.id as g_id, g.name as g_name,
              st.first_name as st_first_name, st.last_name as st_last_name
              FROM rr pr
              INNER JOIN students s ON pr.student_id = s.id
              INNER JOIN student_in_groups sig ON s.id = sig.student_id
              INNER JOIN branch_for_groups bfg ON bfg.id = pr.branch_for_group_id
              INNER JOIN groups g ON g.id = bfg.group_id
              INNER JOIN staff_in_groups stig ON stig.group_id = g.id
              INNER JOIN staff st ON st.id = stig.staff_id
              INNER JOIN branches b ON b.id = bfg.branch_id
              INNER JOIN majors m ON m.id = b.major_id
              INNER JOIN graph_ranges gr ON gr.id = pr.graph_range_id
              WHERE gr.end>='" . $range->getEnd()->format('Y-m-d') . "' 
                  AND stig.type='X'
                  AND s.id IN('" . implode('\',\'', $studentIds) . "')
               ORDER BY m.order, b.order";
        return $this->getReport($sql);
    }

    public function getReportsForGroup($group, DateRange $range)
    {
        $sql = "SELECT s.id as s_id, s.first_name as first_name, s.last_name as last_name,
              pr.id as pr_id, pr.p_raw as pr_perm, pr.e_raw as pr_end, pr.total as pr_total, 
              pr.max as pr_max, pr.redicodi as pr_redicodi, pr.evaluation_count as pr_evcount,
              gr.id as gr_id, 
              gr.start as start, gr.end as end,
              m.id as m_id, m.name as m_name, 
              b.id as b_id, b.name as b_name, bfg.id as bfg_id,
              g.id as g_id, g.name as g_name,
              st.first_name as st_first_name, st.last_name as st_last_name
              FROM rr pr
              INNER JOIN students s ON pr.student_id = s.id
              INNER JOIN student_in_groups sig ON s.id = sig.student_id
              INNER JOIN branch_for_groups bfg ON bfg.id = pr.branch_for_group_id
              INNER JOIN groups g ON g.id = bfg.group_id
              INNER JOIN staff_in_groups stig ON stig.group_id = g.id
              INNER JOIN staff st ON st.id = stig.staff_id
              INNER JOIN branches b ON b.id = bfg.branch_id
              INNER JOIN majors m ON m.id = b.major_id
              INNER JOIN graph_ranges gr ON gr.id = pr.graph_range_id
              WHERE gr.end>='" . $range->getEnd()->format('Y-m-d') . "'
                  AND stig.type='X'
                  AND sig.group_id='" . $group . "'
              ORDER BY m.order, b.order";
        return $this->getReport($sql);
    }


    public function getReportsForStudentsByMajor($studentIds, $range, Major $major)
    {
        // TODO: Implement getReportsForStudentsByMajor() method.
    }

    public function getReportsForGroupByMajor(Group $group, $range, Major $major)
    {
        // TODO: Implement getReportsForGroupByMajor() method.
    }

    private function getReport($sql)
    {
        $rsm = new ResultSetMapping;

        $rsm->addEntityResult(FlatReport::class, 'fr')
            ->addFieldResult('fr', 's_id', 'sId')
            ->addFieldResult('fr', 'first_name', 'sFirstName')
            ->addFieldResult('fr', 'last_name', 'sLastName')
            ->addFieldResult('fr', 'g_id', 'gId')
            ->addFieldResult('fr', 'g_name', 'gName')
            ->addFieldResult('fr', 'st_first_name', 'stFirstName')
            ->addFieldResult('fr', 'st_last_name', 'stLastName')
            ->addFieldResult('fr', 'pr_id', 'prId')
            ->addFieldResult('fr', 'pr_perm', 'prPerm')
            ->addFieldResult('fr', 'pr_end', 'prEnd')
            ->addFieldResult('fr', 'pr_total', 'prTotal')
            ->addFieldResult('fr', 'pr_max', 'prMax')
            ->addFieldResult('fr', 'pr_redicodi', 'prRedicodi')
            ->addFieldResult('fr', 'pr_evcount', 'prEvCount')
            ->addFieldResult('fr', 'gr_id', 'grId')
            ->addFieldResult('fr', 'start', 'grStart')
            ->addFieldResult('fr', 'end', 'grEnd')
            ->addFieldResult('fr', 'b_id', 'bId')
            ->addFieldResult('fr', 'b_name', 'bName')
            ->addFieldResult('fr', 'm_id', 'mId')
            ->addFieldResult('fr', 'm_name', 'mName');

        $query = $this->em->createNativeQuery($sql, $rsm);
        $result = $query->getArrayResult();
        return $result;
    }

    /**
     * @return int
     */
    public function count()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('count(e.id)')
            ->from(Evaluation::class, 'e');
        return $qb->getQuery()->getSingleScalarResult();
    }


}
