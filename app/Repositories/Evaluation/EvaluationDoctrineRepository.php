<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 6/08/16
 * Time: 23:02
 */

namespace App\Repositories\Evaluation;


use App\Domain\Model\Evaluation\Evaluation;
use App\Domain\Model\Evaluation\EvaluationRepository;
use App\Domain\Model\Identity\Group;
use Doctrine\ORM\EntityManager;

class EvaluationDoctrineRepository implements EvaluationRepository
{
    /** @var EntityManager */
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function allEvaluationsForGroup(Group $group)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('e, bfg, b, m, pr')
            ->from(Evaluation::class, 'e')
            ->join('e.branchForGroup', 'bfg')
            ->join('bfg.branch', 'b')
            ->join('b.major', 'm')
            ->join('e.results', 'pr')
            ->where('bfg.group=?1')
            ->setParameter(1, $group->getId());

        return $qb->getQuery()->getResult();
    }
}