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
use App\Domain\Model\Evaluation\EvaluationType;
use App\Domain\Model\Evaluation\Exceptions\EvaluationNotFoundException;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Reporting\FlatComprehensiveReport;
use App\Domain\Model\Reporting\FlatFeedbackReport;
use App\Domain\Model\Reporting\FlatMultiplechoiceReport;
use App\Domain\Model\Reporting\FlatPointReport;
use App\Domain\Model\Reporting\FlatSpokenReport;
use App\Domain\Model\Reporting\FlatStudentRedicodiReport;
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

        return $this->getPointReport($sql);
    }

    /**
     * @param $studentId
     * @param $range
     * @return ArrayCollection
     */
    public function getPointReportForStudent($studentId, $range)
    {
        return $this->getReportsForStudents([$studentId], $range);
    }

    /**
     * @param $studentIds
     * @param DateRange $range
     * @return array
     */
    public function getReportsForStudents($studentIds, DateRange $range)
    {
        $sql = "SELECT  s.id as s_id, s.first_name as first_name, s.last_name as last_name,
              pr.id as pr_id, pr.p_raw as pr_perm, pr.e_raw as pr_end, pr.total as pr_total, 
              pr.max as pr_max, pr.redicodi as pr_redicodi, pr.evaluation_count as pr_evcount,
              gr.id as gr_id, 
              gr.start as start, gr.end as end,
              m.id as m_id, m.name as m_name,  m.order as m_order,
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
               ORDER BY sig.number, m.order, b.order";
        return $this->getPointReport($sql);
    }

    /**
     * @param $group
     * @param DateRange $range
     * @return array
     */
    public function getPointReportForGroup($group, DateRange $range)
    {
        $sql = "SELECT s.id as s_id, s.first_name as first_name, s.last_name as last_name,
              pr.id as pr_id, pr.p_raw as pr_perm, pr.e_raw as pr_end, pr.total as pr_total, 
              pr.max as pr_max, pr.redicodi as pr_redicodi, pr.evaluation_count as pr_evcount,
              gr.id as gr_id, 
              gr.start as start, gr.end as end,
              m.id as m_id, m.name as m_name, m.order as m_order, 
              b.id as b_id, b.name as b_name, bfg.id as bfg_id,
              g.id as g_id, g.name as g_name,
              st.first_name as st_first_name, st.last_name as st_last_name, st.gender as st_gender
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
              ORDER BY sig.number, m.order, b.order";
        return $this->getPointReport($sql);
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
              b.id as b_id, b.name as b_name, bfg.id as bfg_id,
              g.id as g_id, g.name as g_name,
              st.first_name as st_first_name, st.last_name as st_last_name
              FROM comprehensive_results cr
              INNER JOIN evaluations e ON e.id = cr.evaluation_id
              INNER JOIN students s ON cr.student_id = s.id
              INNER JOIN student_in_groups sig ON s.id = sig.student_id
              INNER JOIN branch_for_groups bfg ON bfg.id = e.branch_for_group_id
              INNER JOIN groups g ON g.id = bfg.group_id 
              INNER JOIN staff_in_groups stig ON stig.group_id = g.id
              INNER JOIN staff st ON st.id = stig.staff_id
              INNER JOIN branches b ON b.id = bfg.branch_id
              INNER JOIN majors m ON m.id = b.major_id
              WHERE (e.date BETWEEN '{$start}' AND '{$end}')
                    AND stig.type='X'
                    AND sig.group_id='" . $group . "'
              GROUP BY s.id, bfg.id
              ORDER BY sig.number, m.order, b.order";

        return $this->getComprehensiveReport($sql);
    }

    public function getComprehensiveReportForStudent($studentId, DateRange $range)
    {
        $start = $range->getStart()->format('Y-m-d');
        $end = $range->getEnd()->format('Y-m-d');

        $sql = "SELECT MAX(cr.id) as c_id, s.id as s_id, s.first_name as first_name, s.last_name as last_name,
				COUNT(e.id) AS e_count,
              m.id as m_id, m.name as m_name, m.order as m_order, 
              b.id as b_id, b.name as b_name, bfg.id as bfg_id,
              g.id as g_id, g.name as g_name,
              st.first_name as st_first_name, st.last_name as st_last_name
              FROM comprehensive_results cr
              INNER JOIN evaluations e ON e.id = cr.evaluation_id
              INNER JOIN students s ON cr.student_id = s.id
              INNER JOIN branch_for_groups bfg ON bfg.id = e.branch_for_group_id
              INNER JOIN groups g ON g.id = bfg.group_id 
              INNER JOIN staff_in_groups stig ON stig.group_id = g.id
              INNER JOIN staff st ON st.id = stig.staff_id
              INNER JOIN branches b ON b.id = bfg.branch_id
              INNER JOIN majors m ON m.id = b.major_id
              WHERE (e.date BETWEEN '{$start}' AND '{$end}')
                    AND stig.type='X'
                    AND s.id='{$studentId}'
              GROUP BY bfg.id
              ORDER BY m.order, b.order";

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
              b.id as b_id, b.name as b_name, bfg.id as bfg_id,
              g.id as g_id, g.name as g_name,
              st.first_name as st_first_name, st.last_name as st_last_name
              FROM spoken_results sp
              INNER JOIN evaluations e ON e.id = sp.evaluation_id
              INNER JOIN students s ON sp.student_id = s.id
              INNER JOIN student_in_groups sig ON s.id = sig.student_id
              INNER JOIN branch_for_groups bfg ON bfg.id = e.branch_for_group_id
              INNER JOIN groups g ON g.id = bfg.group_id 
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
    public function getMultiplechoiceReportForGroup($group, DateRange $range)
    {
        $start = $range->getStart()->format('Y-m-d');
        $end = $range->getEnd()->format('Y-m-d');

        $sql = "SELECT mc.id as mc_id, s.id as s_id, s.first_name as first_name, s.last_name as last_name,
				e.settings as e_settings, mc.selected as mc_selected,
              m.id as m_id, m.name as m_name, 
              b.id as b_id, b.name as b_name, bfg.id as bfg_id,
              g.id as g_id, g.name as g_name, m.order as m_order,
              st.first_name as st_first_name, st.last_name as st_last_name
              FROM multiplechoice_results mc
              INNER JOIN evaluations e ON e.id = mc.evaluation_id
              INNER JOIN students s ON mc.student_id = s.id
              INNER JOIN student_in_groups sig ON s.id = sig.student_id
              INNER JOIN branch_for_groups bfg ON bfg.id = e.branch_for_group_id
              INNER JOIN groups g ON g.id = bfg.group_id 
              INNER JOIN staff_in_groups stig ON stig.group_id = g.id
              INNER JOIN staff st ON st.id = stig.staff_id
              INNER JOIN branches b ON b.id = bfg.branch_id
              INNER JOIN majors m ON m.id = b.major_id
              WHERE (e.date BETWEEN '{$start}' AND '{$end}')
                    AND stig.type='X'
                    AND sig.group_id='" . $group . "'
              GROUP BY s.id, bfg.id
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

    public function getRedicodiReportForGroup($group, DateRange $range)
    {
        $rs = $range->getStart()->format('Y-m-d');
        $re = $range->getEnd()->format('Y-m-d');

        $inRangeSql = "rfs.start <= '{$re}' AND rfs.end >= '{$rs}'";

        $sql = "SELECT s.id as s_id, s.first_name as first_name, s.last_name as last_name,
                rfs.redicodi as rfs_redicodi
                FROM redicodi_for_students rfs
                INNER JOIN students s ON s.id = rfs.student_id
                INNER JOIN student_in_groups sig on sig.student_id = s.id
                WHERE ({$inRangeSql})
                    AND sig.group_id = '{$group}'
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
            ->addFieldResult('fr', 'm_id', 'mId')
            ->addFieldResult('fr', 'm_name', 'mName')
            ->addFieldResult('fr', 'm_order', 'mOrder');

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


    public function getSpokenReportForStudent($studentId, DateRange $range)
    {
        // TODO: Implement getSpokenReportForStudent() method.
    }

    public function getMultiplechoiceReportForStudent($studentId, DateRange $range)
    {
        // TODO: Implement getMultiplechoiceReportForStudent() method.
    }

    public function getFeedbackReportForStudent($studentId, DateRange $range)
    {
        // TODO: Implement getFeedbackReportForStudent() method.
    }

    public function getRedicodiReportForStudent($studentId, DateRange $range)
    {
        // TODO: Implement getRedicodiReportForStudent() method.
    }
}
