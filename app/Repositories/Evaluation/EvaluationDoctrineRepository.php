<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 6/08/16
 * Time: 23:02
 */

namespace App\Repositories\Evaluation;


use App\Domain\DTO\StudentDTO;
use App\Domain\DTO\StudentResultsDTO;
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
        $rsm->addEntityResult(StudentDTO::class, 's')
            ->addFieldResult('s', 'id', 'id')
            ->addFieldResult('s', 'first_name', 'firstName')
            ->addFieldResult('s', 'last_name', 'lastName');
        $rsm->addJoinedEntityResult(StudentResultsDTO::class, 'sr', 's', 'results');
        $rsm->addFieldResult('sr', 'pr_id', 'id');
        $rsm->addFieldResult('sr', 'branch', 'branch');
        $rsm->addFieldResult('sr', 'permanent', 'permanent');
        $rsm->addFieldResult('sr', 'result', 'result');
        $rsm->addFieldResult('sr', 'max', 'max');
 
        $sql = "SELECT s.id as id, s.first_name as first_name, s.last_name as last_name,
              pr.id as pr_id, b.name as branch, e.permanent as permanent,
             (SUM(pr.score) / SUM(e.max) * bfg.max) as result, bfg.max as max
            FROM point_results pr
            INNER JOIN evaluations e ON e.id = pr.evaluation_id
            INNER JOIN branch_for_groups bfg ON bfg.id = e.branch_for_group_id
            INNER JOIN branches b ON b.id = bfg.branch_id
            INNER JOIN students s ON s.id = pr.student_id
            GROUP BY pr.student_id, e.permanent, b.id";

        $query = $this->em->createNativeQuery($sql, $rsm);
        $result = $query->getResult();
        return $result;
    }
}