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
use App\Domain\Model\Evaluation\Evaluation;
use App\Domain\Model\Evaluation\EvaluationRepository;
use App\Domain\Model\Evaluation\Exceptions\EvaluationNotFoundException;
use App\Domain\Model\Evaluation\PointResult;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Student;
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
        $rsm = new ResultSetMapping;

        $rsm->addEntityResult(StudentResultDTO::class, 's')
            ->addFieldResult('s', 's_id', 'id')
            ->addFieldResult('s', 'fist_name', 'firstName')
            ->addFieldResult('s', 'last_name', 'lastName');

       /* $rsm->addJoinedEntityResult(MajorResultsDTO::class, 'm', 's', 'majorResults')
            ->addFieldResult('m', 'm_id', 'id')
            ->addFieldResult('m', 'm_name', 'name');*/

        $rsm->addJoinedEntityResult(PointResultDTO::class, 'pr', 's', 'pointResults')
            ->addFieldResult('pr', 'pr_id', 'id')
            ->addFieldResult('pr', 'pr_perm', 'permanentScore')
            ->addFieldResult('pr', 'pr_end', 'endScore')
            ->addFieldResult('pr', 'pr_total', 'totalScore')
            ->addFieldResult('pr', 'pr_max', 'maxScore')
            ->addFieldResult('pr', 'start', 'start')
            ->addFieldResult('pr', 'end', 'end');

        $rsm->addJoinedEntityResult(BranchResultsDTO::class, 'b', 'pr', 'branch')
            ->addFieldResult('b', 'b_id', 'id')
            ->addFieldResult('b', 'b_name', 'name');

        $sql = "SELECT s.id as s_id, s.first_name as first_name, s.last_name as last_name,
              pr.id as pr_id, pr.p_raw as pr_perm, pr.e_raw as pr_end, pr.total as pr_total, 
              pr.max as pr_max, gr.start as start, gr.end as end,
              m.id as m_id, m.name as m_name, 
              b.id as b_id, b.name as b_name,
              gr.id as gr_id, bfg.id as bfg_id
              FROM rr pr
              LEFT JOIN students s ON pr.student_id = s.id
              INNER JOIN branch_for_groups bfg ON bfg.id = pr.branch_for_group_id
              INNER JOIN branches b ON b.id = bfg.branch_id
              INNER JOIN majors m ON m.id = b.major_id
              INNER JOIN graph_ranges gr ON gr.id = pr.graph_range_id";


        /*$sql = "SELECT s.id as s_id, s.first_name as first_name, s.last_name as last_name,
              rr.id as rr_id, rr.p_raw as rr_perm,
              m.id as m_id, m.name as m_name, 
              b.id as b_id, b.name as b_name 
            FROM rr rr
            INNER JOIN branch_for_groups bfg ON bfg.id = rr.branch_for_group_id
            INNER JOIN branches b ON b.id = bfg.branch_id
            INNER JOIN majors m ON m.id = b.major_id
            INNER JOIN students s ON s.id = rr.student_id
            INNER JOIN graph_ranges gr ON gr.id = rr.graph_range_id
            GROUP BY s.id, rr.branch_for_group_id, gr.id";*/

        $query = $this->em->createNativeQuery($sql, $rsm);
        $result = $query->getResult();

        //TODO Remap !!! ???
        //=> Majors / Branch / Range / Result
        return $result;
    }

    public function getReportsForStudents($studentIds, $range)
    {
        // TODO: Implement getReportsForStudents() method.
    }

    public function getReportsForGroup(Group $group, $range)
    {
        // TODO: Implement getReportsForGroup() method.
    }
}