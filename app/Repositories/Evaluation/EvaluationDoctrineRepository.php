<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 6/08/16
 * Time: 23:02
 */

namespace App\Repositories\Evaluation;

use App\Domain\Model\Education\BranchForGroup;
use App\Domain\Model\Education\Major;
use App\Domain\Model\Evaluation\Evaluation;
use App\Domain\Model\Evaluation\EvaluationRepository;
use App\Domain\Model\Evaluation\EvaluationType;
use App\Domain\Model\Evaluation\Exceptions\EvaluationNotFoundException;
use App\Domain\Model\Evaluation\GraphRange;
use App\Domain\Model\Evaluation\RR;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Reporting\FlatComprehensiveReport;
use App\Domain\Model\Reporting\FlatFeedbackReport;
use App\Domain\Model\Reporting\FlatHeaderReport;
use App\Domain\Model\Reporting\FlatMultiplechoiceReport;
use App\Domain\Model\Reporting\FlatPointReport;
use App\Domain\Model\Reporting\FlatSpokenReport;
use App\Domain\Model\Reporting\FlatStudentRedicodiReport;
use App\Domain\Model\Stats\FlatRedicodiStat;
use App\Domain\Model\Time\DateRange;
use App\Domain\NtUid;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Webpatser\Uuid\Uuid;

class EvaluationDoctrineRepository implements EvaluationRepository
{
    /** @var EntityManager */
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /** NEEDED FOR SANITIZING RR tables */
    public function allEvaluations()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('e, bfg')
            ->from(Evaluation::class, 'e')
            ->join('e.branchForGroup', 'bfg');
        $result = $qb->getQuery()->getResult();
        return $result;
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
            ->orderBy('m.order, b.order')
            ->setParameter('groupId', $group->getId())
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    public function get(NtUid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('e, bfg, b, m, pr, s')
            ->from(Evaluation::class, 'e')
            ->join('e.branchForGroup', 'bfg')
            ->join('bfg.branch', 'b')
            ->join('b.major', 'm')
            ->leftJoin('e.pointResults', 'pr')
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

    public function getFeedbackResults(NtUid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('e, bfg, b, m, s, fr')
            ->from(Evaluation::class, 'e')
            ->join('e.branchForGroup', 'bfg')
            ->join('bfg.branch', 'b')
            ->join('b.major', 'm')
            ->leftJoin('e.feedbackResults', 'fr')
            ->leftJoin('fr.student', 's')
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

    public function getMultiplechoiceResults(NtUid $id)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('e, bfg, b, m, s, mcr')
            ->from(Evaluation::class, 'e')
            ->join('e.branchForGroup', 'bfg')
            ->join('bfg.branch', 'b')
            ->join('b.major', 'm')
            ->leftJoin('e.multiplechoiceResults', 'mcr')
            ->leftJoin('mcr.student', 's')
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


    /**
     * @param $id
     * @return EvaluationType
     */
    public function getType(NtUid $id)
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('e, bfg')
            ->from(Evaluation::class, 'e')
            ->join('e.branchForGroup', 'bfg')
            ->where('e.id = :id')
            ->setParameter('id', $id);
        /** @var Evaluation $ev */
        $ev = $qb->getQuery()->getOneOrNullResult();
        if ($ev == null) {
            throw new EvaluationNotFoundException($id);
        }
        return $ev->getEvaluationType();
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
        //TODO: Is this function still used ???

        $sql = "SELECT s.id as s_id, s.first_name as first_name, s.last_name as last_name,
              pr.id as pr_id, pr.p_raw as pr_perm, pr.e_raw as pr_end, pr.total as pr_total, 
              pr.max as pr_max, pr.redicodi as pr_redicodi, pr.evaluation_count as pr_evcount,
              gr.id as gr_id, 
              gr.start as start, gr.end as end,
              m.id as m_id, m.name as m_name, m.order as m_order,
              b.id as b_id, b.name as b_name, b.order as b_order, bfg.id as bfg_id,
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

        return $this->getPointReport($sql);
    }

    /**
     * @param $studentId
     * @param $range
     * @return ArrayCollection
     */
    public function getPointReportForStudent($studentId, $range)
    {
        return $this->getPointReportForStudents([$studentId], $range);
    }

    /**
     * @param $group
     * @param DateRange $range
     * @return array
     */
    public function getPointReportForGroup($group, DateRange $range)
    {
        $start = $range->getStart()->format('Y-m-d');
        $end = $range->getEnd()->format('Y-m-d');

        $sql = "SELECT s.id as s_id, s.first_name as first_name, s.last_name as last_name,
              pr.id as pr_id, pr.p_raw as pr_perm, pr.e_raw as pr_end, pr.total as pr_total, 
              pr.max as pr_max, pr.redicodi as pr_redicodi, pr.evaluation_count as pr_evcount,
              gr.id as gr_id, 
              gr.start as start, gr.end as end,
              m.id as m_id, m.name as m_name, m.order as m_order, 
              b.id as b_id, b.name as b_name, b.order as b_order, bfg.id as bfg_id,
              g.id as g_id, g.name as g_name,
              st.first_name as st_first_name, st.last_name as st_last_name, st.gender as st_gender
              FROM rr pr
              INNER JOIN students s ON pr.student_id = s.id
              INNER JOIN student_in_groups sig ON s.id = sig.student_id
              INNER JOIN branch_for_groups bfg ON bfg.id = pr.branch_for_group_id
              INNER JOIN groups g ON (g.id = sig.group_id AND g.id = bfg.group_id)
              INNER JOIN staff_in_groups stig ON stig.group_id = g.id
              INNER JOIN staff st ON st.id = stig.staff_id
              INNER JOIN branches b ON b.id = bfg.branch_id
              INNER JOIN majors m ON m.id = b.major_id
              INNER JOIN graph_ranges gr ON gr.id = pr.graph_range_id
               WHERE gr.id = (SELECT igr.id 
                             FROM graph_ranges igr 
                             WHERE igr.start <='{$end}' AND igr.end >= '{$start}' 
                              AND (igr.level_id = g.level_id OR igr.level_id IS NULL)
                             ORDER BY igr.end DESC, igr.level_id DESC LIMIT 1) 
                  AND stig.type='X'
                  AND stig.start <='{$end}' AND stig.end >= '{$start}'
                  AND sig.group_id='{$group}'
              ORDER BY gr.end DESC, gr.level_id DESC, sig.number, m.order, b.order";
        return $this->getPointReport($sql);
    }

    /**
     * @param $studentIds
     * @param DateRange $range
     * @return array
     */
    public function getPointReportForStudents($studentIds, DateRange $range)
    {
        $start = $range->getStart()->format('Y-m-d');
        $end = $range->getEnd()->format('Y-m-d');
        $ids = implode('\',\'', $studentIds);

        $sql = "SELECT s.id as s_id, s.first_name as first_name, s.last_name as last_name,
              pr.id as pr_id, pr.p_raw as pr_perm, pr.e_raw as pr_end, pr.total as pr_total, 
              pr.max as pr_max, pr.redicodi as pr_redicodi, pr.evaluation_count as pr_evcount,
              gr.id as gr_id, 
              gr.start as start, gr.end as end,
              m.id as m_id, m.name as m_name, m.order as m_order, 
              b.id as b_id, b.name as b_name, b.order as b_order, bfg.id as bfg_id,
              g.id as g_id, g.name as g_name,
              st.first_name as st_first_name, st.last_name as st_last_name, st.gender as st_gender
              FROM rr pr
              INNER JOIN students s ON pr.student_id = s.id
              INNER JOIN student_in_groups sig ON s.id = sig.student_id
              INNER JOIN branch_for_groups bfg ON bfg.id = pr.branch_for_group_id
              INNER JOIN groups g ON (g.id = sig.group_id AND g.id = bfg.group_id)
              INNER JOIN staff_in_groups stig ON stig.group_id = g.id
              INNER JOIN staff st ON st.id = stig.staff_id
              INNER JOIN branches b ON b.id = bfg.branch_id
              INNER JOIN majors m ON m.id = b.major_id
              INNER JOIN graph_ranges gr ON gr.id = pr.graph_range_id
              WHERE gr.id = (SELECT igr.id 
                             FROM graph_ranges igr 
                             WHERE igr.start <='{$end}' AND igr.end >= '{$start}' 
                              AND (igr.level_id = g.level_id OR igr.level_id IS NULL)
                             ORDER BY igr.end DESC, igr.level_id DESC LIMIT 1)   
                  AND stig.type='X'
                  AND stig.start <='{$end}' AND stig.end >= '{$start}'
                  AND s.id IN('{$ids}')
                  AND (sig.end IS NULL OR sig.end >='{$end}')
               ORDER BY gr.end DESC, gr.level_id DESC, sig.number, m.order, b.order";
        return $this->getPointReport($sql);
    }

    public function getHistory($studentIds)
    {
        $ids = implode('\',\'', $studentIds);

        $sql = "SELECT pr.student_id as s_id,
              pr.id as pr_id, pr.p_raw as pr_perm, pr.e_raw as pr_end, pr.total as pr_total, 
              pr.max as pr_max, pr.redicodi as pr_redicodi, pr.evaluation_count as pr_evcount,
              gr.id as gr_id, 
              gr.start as start, gr.end as end,
              m.id as m_id, m.name as m_name, m.order as m_order, 
              b.id as b_id, b.name as b_name, b.order as b_order, bfg.id as bfg_id
              FROM rr pr
              INNER JOIN students s ON pr.student_id = s.id
              INNER JOIN branch_for_groups bfg ON bfg.id = pr.branch_for_group_id
              INNER JOIN branches b ON b.id = bfg.branch_id
              INNER JOIN majors m ON m.id = b.major_id
              INNER JOIN graph_ranges gr ON gr.id = pr.graph_range_id
              WHERE s.id IN('{$ids}')
               ORDER BY gr.end DESC, gr.start DESC, m.order, b.order";
        return $this->getHistoryReport($sql);
    }

    public function getHistoryForGroup($group, DateRange $range)
    {
        $sql = "SELECT s.id as s_id,
              pr.id as pr_id, pr.p_raw as pr_perm, pr.e_raw as pr_end, pr.total as pr_total, 
              pr.max as pr_max, pr.redicodi as pr_redicodi, pr.evaluation_count as pr_evcount,
              gr.id as gr_id, 
              gr.start as start, gr.end as end,
              m.id as m_id, m.name as m_name, m.order as m_order, 
              b.id as b_id, b.name as b_name, b.order as b_order, bfg.id as bfg_id,
              g.id as g_id, g.name as g_name
              FROM rr pr
              INNER JOIN students s ON pr.student_id = s.id
              INNER JOIN student_in_groups sig ON s.id = sig.student_id
              INNER JOIN branch_for_groups bfg ON bfg.id = pr.branch_for_group_id
              INNER JOIN groups g ON (g.id = sig.group_id AND g.id = bfg.group_id)
              INNER JOIN staff_in_groups stig ON stig.group_id = g.id
              INNER JOIN staff st ON st.id = stig.staff_id
              INNER JOIN branches b ON b.id = bfg.branch_id
              INNER JOIN majors m ON m.id = b.major_id
              INNER JOIN graph_ranges gr ON gr.id = pr.graph_range_id
               WHERE sig.group_id='{$group}'
                AND (sig.end IS NULL OR sig.end >= '{$range->getEnd()->format('Y-m-d')}')
              ORDER BY gr.end DESC, gr.start DESC, sig.number, m.order, b.order";
        return $this->getHistoryReport($sql);
    }

    public function getHeadersReportForGroup($group, DateRange $range)
    {
        $start = $range->getStart()->format('Y-m-d');
        $end = $range->getEnd()->format('Y-m-d');

        $sql = "SELECT MAX(pr.id) as hr_id,
                    AVG(pr.e_raw) AS avg_e_raw, AVG(pr.p_raw) AS avg_p_raw, AVG(pr.total) AS avg_total, 
                    pr.max as pr_max,
                    gr.id as gr_id, 
                    gr.start as start, gr.end as end,
                    m.id as m_id, m.name as m_name, m.order as m_order, 
                    b.id as b_id, b.name as b_name, b.order as b_order, bfg.id as bfg_id,
                    g.id as g_id, g.name as g_name
                    FROM rr pr
                    INNER JOIN branch_for_groups bfg ON bfg.id = pr.branch_for_group_id
                    INNER JOIN groups g ON g.id = bfg.group_id
                    INNER JOIN branches b ON b.id = bfg.branch_id
                    INNER JOIN majors m ON m.id = b.major_id
                    INNER JOIN graph_ranges gr ON gr.id = pr.graph_range_id
                    WHERE gr.start <='{$end}' AND gr.end >= '{$start}'
                        AND g.id = '{$group}'
                    GROUP BY branch_for_group_id
                    ORDER BY m.order, b.order";
        return $this->getHeaderReport($sql);
    }

    /**
     * @param $group
     * @param $range
     * @return mixed
     */
    public function getComprehensiveReportForGroup($group, DateRange $range)
    {
        $start = $range->getStart()->format('Y-m-d');
        $end = $range->getEnd()->format('Y-m-d');

        $sql = "SELECT MAX(cr.id) as c_id, s.id as s_id, s.first_name as first_name, s.last_name as last_name,
				COUNT(e.id) AS e_count,
              m.id as m_id, m.name as m_name, m.order as m_order, 
              b.id as b_id, b.name as b_name, b.order as b_order, bfg.id as bfg_id,
              g.id as g_id, g.name as g_name,
              st.first_name as st_first_name, st.last_name as st_last_name
              FROM comprehensive_results cr
              INNER JOIN evaluations e ON e.id = cr.evaluation_id
              INNER JOIN students s ON cr.student_id = s.id
              INNER JOIN student_in_groups sig ON s.id = sig.student_id
              INNER JOIN branch_for_groups bfg ON bfg.id = e.branch_for_group_id
              INNER JOIN groups g ON (g.id = sig.group_id AND g.id = bfg.group_id)
              INNER JOIN staff_in_groups stig ON stig.group_id = g.id
              INNER JOIN staff st ON st.id = stig.staff_id
              INNER JOIN branches b ON b.id = bfg.branch_id
              INNER JOIN majors m ON m.id = b.major_id
              WHERE (e.date BETWEEN '{$start}' AND '{$end}')
                    AND stig.type='X'
                    AND stig.start <='{$end}' AND stig.end >= '{$start}'
                    AND sig.group_id='{$group}'
              GROUP BY s.id, bfg.id
              ORDER BY sig.number, m.order, b.order";

        return $this->getComprehensiveReport($sql);
    }

    public function getComprehensiveReportForStudents($studentIds, DateRange $range)
    {
        $start = $range->getStart()->format('Y-m-d');
        $end = $range->getEnd()->format('Y-m-d');

        $ids = implode('\',\'', $studentIds);

        $sql = "SELECT MAX(cr.id) as c_id, s.id as s_id, s.first_name as first_name, s.last_name as last_name,
				COUNT(e.id) AS e_count,
              m.id as m_id, m.name as m_name, m.order as m_order, 
              b.id as b_id, b.name as b_name, b.order as b_order, bfg.id as bfg_id,
              g.id as g_id, g.name as g_name,
              st.first_name as st_first_name, st.last_name as st_last_name
              FROM comprehensive_results cr
              INNER JOIN evaluations e ON e.id = cr.evaluation_id
              INNER JOIN students s ON cr.student_id = s.id
              INNER JOIN student_in_groups sig ON s.id = sig.student_id
              INNER JOIN branch_for_groups bfg ON bfg.id = e.branch_for_group_id
              INNER JOIN groups g ON (g.id = sig.group_id AND g.id = bfg.group_id)
              INNER JOIN staff_in_groups stig ON stig.group_id = g.id
              INNER JOIN staff st ON st.id = stig.staff_id
              INNER JOIN branches b ON b.id = bfg.branch_id
              INNER JOIN majors m ON m.id = b.major_id
              WHERE (e.date BETWEEN '{$start}' AND '{$end}')
                    AND stig.type='X'
                    AND stig.start <='{$end}' AND stig.end >= '{$start}'
                    AND s.id  IN ('{$ids}')
              GROUP BY s.id, bfg.id
              ORDER BY sig.number, m.order, b.order";

        return $this->getComprehensiveReport($sql);
    }

    /**
     * @param $group
     * @param $range
     * @return mixed
     */
    public function getSpokenReportForGroup($group, DateRange $range)
    {
        $start = $range->getStart()->format('Y-m-d');
        $end = $range->getEnd()->format('Y-m-d');


        $sql = "SELECT MAX(sp.id) as sp_id, s.id as s_id, s.first_name as first_name, s.last_name as last_name,
				COUNT(e.id) AS e_count,
              m.id as m_id, m.name as m_name, m.order as m_order, 
              b.id as b_id, b.name as b_name, b.order as b_order, bfg.id as bfg_id,
              g.id as g_id, g.name as g_name,
              st.first_name as st_first_name, st.last_name as st_last_name
              FROM spoken_results sp
              INNER JOIN evaluations e ON e.id = sp.evaluation_id
              INNER JOIN students s ON sp.student_id = s.id
              INNER JOIN student_in_groups sig ON s.id = sig.student_id
              INNER JOIN branch_for_groups bfg ON bfg.id = e.branch_for_group_id
              INNER JOIN groups g ON (g.id = sig.group_id AND g.id = bfg.group_id)
              INNER JOIN staff_in_groups stig ON stig.group_id = g.id
              INNER JOIN staff st ON st.id = stig.staff_id
              INNER JOIN branches b ON b.id = bfg.branch_id
              INNER JOIN majors m ON m.id = b.major_id
              WHERE (e.date BETWEEN '{$start}' AND '{$end}')
                    AND stig.type='X'
                    AND sig.group_id='" . $group . "'
              GROUP BY s.id, bfg.id
              ORDER BY sig.number, m.order, b.order";

        return $this->getSpokenReport($sql);
    }


    /**
     * @param $group
     * @param $range
     * @return mixed
     */
    public function getSpokenReportForStudents($studentIds, DateRange $range)
    {
        $start = $range->getStart()->format('Y-m-d');
        $end = $range->getEnd()->format('Y-m-d');
        $ids = implode('\',\'', $studentIds);

        $sql = "SELECT MAX(sp.id) as sp_id, s.id as s_id, s.first_name as first_name, s.last_name as last_name,
				COUNT(e.id) AS e_count,
              m.id as m_id, m.name as m_name, m.order as m_order, 
              b.id as b_id, b.name as b_name, b.order as b_order, bfg.id as bfg_id,
              g.id as g_id, g.name as g_name,
              st.first_name as st_first_name, st.last_name as st_last_name
              FROM spoken_results sp
              INNER JOIN evaluations e ON e.id = sp.evaluation_id
              INNER JOIN students s ON sp.student_id = s.id
              INNER JOIN student_in_groups sig ON s.id = sig.student_id
              INNER JOIN branch_for_groups bfg ON bfg.id = e.branch_for_group_id
              INNER JOIN groups g ON (g.id = sig.group_id AND g.id = bfg.group_id)
              INNER JOIN staff_in_groups stig ON stig.group_id = g.id
              INNER JOIN staff st ON st.id = stig.staff_id
              INNER JOIN branches b ON b.id = bfg.branch_id
              INNER JOIN majors m ON m.id = b.major_id
              WHERE (e.date BETWEEN '{$start}' AND '{$end}')
                    AND stig.type='X'
                    AND s.id IN('{$ids}')
              GROUP BY s.id, bfg.id
              ORDER BY sig.number, m.order, b.order";

        return $this->getSpokenReport($sql);
    }

    /**
     * @param $group
     * @param $range
     * @return mixed
     */
    public function getMultiplechoiceReportForGroup($group, DateRange $range)
    {
        $start = $range->getStart()->format('Y-m-d');
        $end = $range->getEnd()->format('Y-m-d');

        $sql = "SELECT mc.id as mc_id, s.id as s_id, s.first_name as first_name, s.last_name as last_name,
				e.settings as e_settings, mc.selected as mc_selected,
              m.id as m_id, m.name as m_name, 
              b.id as b_id, b.name as b_name, b.order as b_order, bfg.id as bfg_id,
              g.id as g_id, g.name as g_name, m.order as m_order,
              st.first_name as st_first_name, st.last_name as st_last_name
              FROM multiplechoice_results mc
              INNER JOIN evaluations e ON e.id = mc.evaluation_id
              INNER JOIN students s ON mc.student_id = s.id
              INNER JOIN student_in_groups sig ON s.id = sig.student_id
              INNER JOIN branch_for_groups bfg ON bfg.id = e.branch_for_group_id
              INNER JOIN groups g ON (g.id = sig.group_id AND g.id = bfg.group_id)
              INNER JOIN staff_in_groups stig ON stig.group_id = g.id
              INNER JOIN staff st ON st.id = stig.staff_id
              INNER JOIN branches b ON b.id = bfg.branch_id
              INNER JOIN majors m ON m.id = b.major_id
              WHERE (e.date BETWEEN '{$start}' AND '{$end}')
                    AND stig.type='X'
                    AND sig.group_id='{$group}'
              ORDER BY sig.number, m.order, b.order";

        return $this->getMultiplechoiceReport($sql);
    }

    public function getMultiplechoiceReportForStudents($studentIds, DateRange $range)
    {
        $start = $range->getStart()->format('Y-m-d');
        $end = $range->getEnd()->format('Y-m-d');

        $ids = implode('\',\'', $studentIds);

        $sql = "SELECT mc.id as mc_id, s.id as s_id, s.first_name as first_name, s.last_name as last_name,
				e.settings as e_settings, mc.selected as mc_selected,
              m.id as m_id, m.name as m_name, 
              b.id as b_id, b.name as b_name, b.order as b_order, bfg.id as bfg_id,
              g.id as g_id, g.name as g_name, m.order as m_order,
              st.first_name as st_first_name, st.last_name as st_last_name
              FROM multiplechoice_results mc
              INNER JOIN evaluations e ON e.id = mc.evaluation_id
              INNER JOIN students s ON mc.student_id = s.id
              INNER JOIN student_in_groups sig ON s.id = sig.student_id
              INNER JOIN branch_for_groups bfg ON bfg.id = e.branch_for_group_id
              INNER JOIN groups g ON (g.id = sig.group_id AND g.id = bfg.group_id)
              INNER JOIN staff_in_groups stig ON stig.group_id = g.id
              INNER JOIN staff st ON st.id = stig.staff_id
              INNER JOIN branches b ON b.id = bfg.branch_id
              INNER JOIN majors m ON m.id = b.major_id
              WHERE (e.date BETWEEN '{$start}' AND '{$end}')
                    AND stig.type='X'
                    AND s.id IN('{$ids}')
              ORDER BY sig.number, m.order, b.order";

        return $this->getMultiplechoiceReport($sql);
    }


    public function getFeedbackReportForGroup($group, DateRange $range)
    {
        $start = $range->getStart()->format('Y-m-d');
        $end = $range->getEnd()->format('Y-m-d');

        $sql = "SELECT s.id as s_id, s.first_name as first_name, s.last_name as last_name,
                CONCAT(fr.summary) as fr_summary
                FROM feedback_results as fr
                INNER JOIN evaluations e on e.id = fr.evaluation_id
                INNER JOIN students s on s.id = fr.student_id
                INNER JOIN student_in_groups sig on sig.student_id = s.id
                WHERE (e.date BETWEEN '{$start}' AND '{$end}')
                    AND sig.group_id = '" . $group . "'
                GROUP BY s.id
                ORDER BY sig.number";
        return $this->getFeedbackReport($sql);
    }

    public function getFeedbackReportForStudents($studentIds, DateRange $range)
    {
        $start = $range->getStart()->format('Y-m-d');
        $end = $range->getEnd()->format('Y-m-d');

        $ids = implode('\',\'', $studentIds);

        $sql = "SELECT s.id as s_id, s.first_name as first_name, s.last_name as last_name,
                CONCAT(fr.summary) as fr_summary
                FROM feedback_results as fr
                INNER JOIN evaluations e on e.id = fr.evaluation_id
                INNER JOIN students s on s.id = fr.student_id
                INNER JOIN student_in_groups sig on sig.student_id = s.id
                WHERE (e.date BETWEEN '{$start}' AND '{$end}')
                    AND s.id IN('{$ids}')
                GROUP BY s.id
                ORDER BY sig.number";
        return $this->getFeedbackReport($sql);
    }

    public function getRedicodiReportForGroup($group, DateRange $range)
    {
        $rs = $range->getStart()->format('Y-m-d');
        $re = $range->getEnd()->format('Y-m-d');

        $sql = "SELECT s.id as s_id, s.first_name as first_name, s.last_name as last_name,
                rfs.redicodi as rfs_redicodi
                FROM redicodi_for_students rfs
                INNER JOIN students s ON s.id = rfs.student_id
                INNER JOIN student_in_groups sig on sig.student_id = s.id
                WHERE (rfs.start <= '{$re}' AND rfs.end >= '{$rs}')
                    AND sig.group_id = '{$group}'
                GROUP BY s.id, rfs.redicodi
                ORDER BY sig.number";

        return $this->getRedicodiReport($sql);
    }

    public function getRedicodiReportForStudents($studentIds, DateRange $range)
    {
        $rs = $range->getStart()->format('Y-m-d');
        $re = $range->getEnd()->format('Y-m-d');

        $ids = implode('\',\'', $studentIds);

        $sql = "SELECT s.id as s_id, s.first_name as first_name, s.last_name as last_name,
                rfs.redicodi as rfs_redicodi
                FROM redicodi_for_students rfs
                INNER JOIN students s ON s.id = rfs.student_id
                INNER JOIN student_in_groups sig on sig.student_id = s.id
                WHERE (rfs.start <= '{$re}' AND rfs.end >= '{$rs}')
                    AND s.id IN('{$ids}')
                GROUP BY s.id, rfs.redicodi
                ORDER BY sig.number";

        return $this->getRedicodiReport($sql);
    }

    public function getReportsForStudentsByMajor($studentIds, $range, Major $major)
    {
        // TODO: Implement getReportsForStudentsByMajor() method.
    }

    public function getReportsForGroupByMajor(Group $group, $range, Major $major)
    {
        // TODO: Implement getReportsForGroupByMajor() method.
    }


#region MAPPERS
    private function getPointReport($sql)
    {
        $rsm = new ResultSetMapping;

        $rsm->addEntityResult(FlatPointReport::class, 'fr')
            ->addFieldResult('fr', 's_id', 'sId')
            ->addFieldResult('fr', 'first_name', 'sFirstName')
            ->addFieldResult('fr', 'last_name', 'sLastName')
            ->addFieldResult('fr', 'g_id', 'gId')
            ->addFieldResult('fr', 'g_name', 'gName')
            ->addFieldResult('fr', 'st_first_name', 'stFirstName')
            ->addFieldResult('fr', 'st_last_name', 'stLastName')
            ->addFieldResult('fr', 'st_gender', 'stGender')
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
            ->addFieldResult('fr', 'b_order', 'bOrder')
            ->addFieldResult('fr', 'm_id', 'mId')
            ->addFieldResult('fr', 'm_name', 'mName')
            ->addFieldResult('fr', 'm_order', 'mOrder');

        $query = $this->em->createNativeQuery($sql, $rsm);
        $result = $query->getArrayResult();

        return $result;
    }

    private function getHistoryReport($sql)
    {
        $rsm = new ResultSetMapping;

        $rsm->addEntityResult(FlatPointReport::class, 'fr')
            ->addFieldResult('fr', 's_id', 'sId')
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
            ->addFieldResult('fr', 'b_order', 'bOrder')
            ->addFieldResult('fr', 'm_id', 'mId')
            ->addFieldResult('fr', 'm_name', 'mName')
            ->addFieldResult('fr', 'm_order', 'mOrder');

        $query = $this->em->createNativeQuery($sql, $rsm);
        $result = $query->getArrayResult();

        return $result;
    }

    private function getHeaderReport($sql)
    {
        $rsm = new ResultSetMapping;
        $rsm->addEntityResult(FlatHeaderReport::class, 'hr')
            ->addFieldResult('hr', 'hr_id', 'hrId')
            ->addFieldResult('hr', 'b_id', 'bId')
            ->addFieldResult('hr', 'b_name', 'bName')
            ->addFieldResult('hr', 'b_order', 'bOrder')
            ->addFieldResult('hr', 'm_id', 'mId')
            ->addFieldResult('hr', 'm_name', 'mName')
            ->addFieldResult('hr', 'm_order', 'mOrder')
            ->addFieldResult('hr', 'g_id', 'gId')
            ->addFieldResult('hr', 'g_name', 'gName')
            ->addFieldResult('hr', 'avg_e_raw', 'avgEnd')
            ->addFieldResult('hr', 'avg_p_raw', 'avgPermanent')
            ->addFieldResult('hr', 'avg_total', 'avgTotal')
            ->addFieldResult('hr', 'pr_max', 'prMax');

        $query = $this->em->createNativeQuery($sql, $rsm);
        $result = $query->getArrayResult();
        return $result;
    }

    private function getComprehensiveReport($sql)
    {
        $rsm = new ResultSetMapping;

        $rsm->addEntityResult(FlatComprehensiveReport::class, 'cr')
            ->addFieldResult('cr', 'c_id', 'cId')
            ->addFieldResult('cr', 's_id', 'sId')
            ->addFieldResult('cr', 'first_name', 'sFirstName')
            ->addFieldResult('cr', 'last_name', 'sLastName')
            ->addFieldResult('cr', 'g_id', 'gId')
            ->addFieldResult('cr', 'g_name', 'gName')
            ->addFieldResult('cr', 'st_first_name', 'stFirstName')
            ->addFieldResult('cr', 'st_last_name', 'stLastName')
            ->addFieldResult('cr', 'st_gender', 'stGender')
            ->addFieldResult('cr', 'e_count', 'eCount')
            ->addFieldResult('cr', 'b_id', 'bId')
            ->addFieldResult('cr', 'b_name', 'bName')
            ->addFieldResult('cr', 'b_order', 'bOrder')
            ->addFieldResult('cr', 'm_id', 'mId')
            ->addFieldResult('cr', 'm_name', 'mName')
            ->addFieldResult('cr', 'm_order', 'mOrder');

        $query = $this->em->createNativeQuery($sql, $rsm);
        $result = $query->getArrayResult();
        return $result;
    }

    private function getSpokenReport($sql)
    {
        $rsm = new ResultSetMapping;

        $rsm->addEntityResult(FlatSpokenReport::class, 'spr')
            ->addFieldResult('spr', 'sp_id', 'spId')
            ->addFieldResult('spr', 's_id', 'sId')
            ->addFieldResult('spr', 'first_name', 'sFirstName')
            ->addFieldResult('spr', 'last_name', 'sLastName')
            ->addFieldResult('spr', 'g_id', 'gId')
            ->addFieldResult('spr', 'g_name', 'gName')
            ->addFieldResult('spr', 'st_first_name', 'stFirstName')
            ->addFieldResult('spr', 'st_last_name', 'stLastName')
            ->addFieldResult('spr', 'st_gender', 'stGender')
            ->addFieldResult('spr', 'e_count', 'eCount')
            ->addFieldResult('spr', 'b_id', 'bId')
            ->addFieldResult('spr', 'b_name', 'bName')
            ->addFieldResult('spr', 'b_order', 'bOrder')
            ->addFieldResult('spr', 'm_id', 'mId')
            ->addFieldResult('spr', 'm_name', 'mName')
            ->addFieldResult('spr', 'm_order', 'mOrder');

        $query = $this->em->createNativeQuery($sql, $rsm);
        $result = $query->getArrayResult();
        return $result;
    }

    private function getMultiplechoiceReport($sql)
    {
        $rsm = new ResultSetMapping();

        $rsm->addEntityResult(FlatMultiplechoiceReport::class, 'mc')
            ->addFieldResult('mc', 'mc_id', 'mcId')
            ->addFieldResult('mc', 's_id', 'sId')
            ->addFieldResult('mc', 'first_name', 'sFirstName')
            ->addFieldResult('mc', 'last_name', 'sLastName')
            ->addFieldResult('mc', 'e_settings', 'eSettings')
            ->addFieldResult('mc', 'mc_selected', 'mcSelected')
            ->addFieldResult('mc', 'g_id', 'gId')
            ->addFieldResult('mc', 'g_name', 'gName')
            ->addFieldResult('mc', 'st_first_name', 'stFirstName')
            ->addFieldResult('mc', 'st_last_name', 'stLastName')
            ->addFieldResult('mc', 'st_gender', 'stGender')
            ->addFieldResult('mc', 'b_id', 'bId')
            ->addFieldResult('mc', 'b_name', 'bName')
            ->addFieldResult('mc', 'b_order', 'bOrder')
            ->addFieldResult('mc', 'm_id', 'mId')
            ->addFieldResult('mc', 'm_name', 'mName')
            ->addFieldResult('mc', 'm_order', 'mOrder');

        $query = $this->em->createNativeQuery($sql, $rsm);
        $result = $query->getArrayResult();
        return $result;
    }

    private function getFeedbackReport($sql)
    {
        $rsm = new ResultSetMapping;

        $rsm->addEntityResult(FlatFeedbackReport::class, 'fr')
            ->addFieldResult('fr', 's_id', 'sId')
            ->addFieldResult('fr', 'first_name', 'sFirstName')
            ->addFieldResult('fr', 'last_name', 'sLastName')
            ->addFieldResult('fr', 'fr_summary', 'frSummary');

        $query = $this->em->createNativeQuery($sql, $rsm);
        $result = $query->getArrayResult();
        return $result;
    }

    private function getRedicodiReport($sql)
    {
        $rsm = new ResultSetMapping();

        $rsm->addEntityResult(FlatStudentRedicodiReport::class, "fsr")
            ->addFieldResult('fsr', 's_id', 'sId')
            ->addFieldResult('fsr', 'first_name', 'sFirstName')
            ->addFieldResult('fsr', 'last_name', 'sLastName')
            ->addFieldResult('fsr', 'rfs_redicodi', 'rfsRedicodi');

        $query = $this->em->createNativeQuery($sql, $rsm);
        $result = $query->getArrayResult();
        return $result;
    }

#endregion

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


    /**
     * @param $evaluation
     * @return boolean
     */
    public function remove(Evaluation $evaluation)
    {
        $this->em->remove($evaluation);
        $this->em->flush();
        return true;
    }


    public function allRedicodiStats(DateTime $endDate)
    {
        $end = $endDate->format('Y-m-d');
        $rsm = new ResultSetMapping;

        $rsm->addEntityResult(FlatRedicodiStat::class, 'rs')
            ->addFieldResult('rs', 'rs_id', 'id')
            ->addFieldResult('rs', 'rs_count', 'count')
            ->addFieldResult('rs', 'rs_redicodi', 'redicodi');

        $sql = "SELECT temp.student_id AS rs_id, COUNT(temp.student_id) AS rs_count, temp.redicodi AS rs_redicodi
                  FROM 
	                (SELECT DISTINCT rfs.student_id, rfs.redicodi FROM redicodi_for_students rfs WHERE rfs.end >= '{$end}') temp
                  GROUP BY temp.redicodi;";

        $query = $this->em->createNativeQuery($sql, $rsm);
        $result = $query->getArrayResult();
        return $result;

    }

    /* ***************************************************
     * CALCULATIONS
     * **************************************************/
    public function allRangeResults(GraphRange $graphRange, BranchForGroup $branchForGroup)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('rr')
            ->from(RR::class, 'rr')
            ->where('rr.graphRange=:grid')
            ->andWhere('rr.branchForGroup=:bfgid')
            ->setParameter('grid', $graphRange->getId())
            ->setParameter('bfgid', $branchForGroup->getId());

        $results = $qb->getQuery()->getResult();
        return $results;
    }

    public function allPointResults(GraphRange $graphRange, BranchForGroup $branchForGroup)
    {
        $sql = "
        SELECT pr.student_id AS student_id, 
          (SUM(pr.score)/SUM(e.max)) * bfg.max  AS raw_score, 
          e.permanent AS permanent,
          COUNT(pr.id) AS ev_count,
          GROUP_CONCAT(pr.redicodi) AS redicodi,
          bfg.max AS max
        FROM point_results pr
            INNER JOIN evaluations e on pr.evaluation_id = e.id
            INNER JOIN branch_for_groups bfg on e.branch_for_group_id=bfg.id
        WHERE e.branch_for_group_id='{$branchForGroup->getId()}'
            AND e.date >= '{$graphRange->getStart()->format('Y-m-d')}'
            AND e.date <= '{$graphRange->getEnd()->format('Y-m-d')}'
        GROUP BY pr.student_id, e.permanent
        ORDER BY pr.student_id
                
        ";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }


    public function updateOrCreateRR(RR $rr)
    {
        $this->em->persist($rr);
        $this->em->flush();
        return 1;
    }


}
