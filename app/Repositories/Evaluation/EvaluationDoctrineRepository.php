<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 6/08/16
 * Time: 23:02
 */

namespace App\Repositories\Evaluation;


use App\Domain\DTO\Results\BranchResultsDTO;
use App\Domain\DTO\Results\MajorResultsDTO;
use App\Domain\DTO\Results\PointResultDTO;
use App\Domain\DTO\Results\StudentResultDTO;
use App\Domain\Model\Education\Major;
use App\Domain\Model\Evaluation\Evaluation;
use App\Domain\Model\Evaluation\EvaluationRepository;
use App\Domain\Model\Evaluation\Exceptions\EvaluationNotFoundException;
use App\Domain\Model\Evaluation\PointResult;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Reporting\FlatReport;
use App\Domain\NtUid;
use DateTime;
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

        $qb->select('e, bfg, b, m, pr')
            ->from(Evaluation::class, 'e')
            ->join('e.branchForGroup', 'bfg')
            ->join('bfg.branch', 'b')
            ->join('b.major', 'm')
            ->leftJoin('e.pointResults', 'pr')
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

    public function getSummary()
    {
        $sql = "SELECT s.id as s_id, s.first_name as first_name, s.last_name as last_name,
              pr.id as pr_id, pr.p_raw as pr_perm, pr.e_raw as pr_end, pr.total as pr_total, 
              pr.max as pr_max, pr.redicodi as pr_redicodi, pr.evaluation_count as pr_evcount,
              gr.id as gr_id, 
              gr.start as start, gr.end as end,
              m.id as m_id, m.name as m_name, 
              b.id as b_id, b.name as b_name, bfg.id as bfg_id
              FROM rr pr
              INNER JOIN students s ON pr.student_id = s.id
              INNER JOIN student_in_groups sig ON s.id = sig.student_id
              INNER JOIN branch_for_groups bfg ON bfg.id = pr.branch_for_group_id
              INNER JOIN branches b ON b.id = bfg.branch_id
              INNER JOIN majors m ON m.id = b.major_id
              INNER JOIN graph_ranges gr ON gr.id = pr.graph_range_id";

        return $this->getReport($sql);
    }

    public function getReportsForStudents($studentIds, $range)
    {

    }

    public function getReportsForGroup(NtUid $group, $range)
    {
        $sql = "SELECT s.id as s_id, s.first_name as first_name, s.last_name as last_name,
              pr.id as pr_id, pr.p_raw as pr_perm, pr.e_raw as pr_end, pr.total as pr_total, 
              pr.max as pr_max, pr.redicodi as pr_redicodi, pr.evaluation_count as pr_evcount,
              gr.id as gr_id, 
              gr.start as start, gr.end as end,
              m.id as m_id, m.name as m_name, 
              b.id as b_id, b.name as b_name, bfg.id as bfg_id
              FROM rr pr
              INNER JOIN students s ON pr.student_id = s.id
              INNER JOIN student_in_groups sig ON s.id = sig.student_id
              INNER JOIN branch_for_groups bfg ON bfg.id = pr.branch_for_group_id
              INNER JOIN branches b ON b.id = bfg.branch_id
              INNER JOIN majors m ON m.id = b.major_id
              INNER JOIN graph_ranges gr ON gr.id = pr.graph_range_id
              WHERE sig.group_id='" . $group . "'";
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

    private function getReport($sql) {
        $rsm = new ResultSetMapping;

        $rsm->addEntityResult(FlatReport::class, 'fr')
            ->addFieldResult('fr', 's_id', 'sId')
            ->addFieldResult('fr', 'first_name', 'sFirstName')
            ->addFieldResult('fr', 'last_name', 'sLastName')
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
}
