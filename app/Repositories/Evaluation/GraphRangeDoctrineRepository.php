<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/06/17
 * Time: 09:35
 */

namespace App\Repositories\Evaluation;


use App\Domain\Model\Evaluation\GraphRange;
use App\Domain\Model\Evaluation\GraphRangeRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

class GraphRangeDoctrineRepository implements GraphRangeRepository
{
    /** @var EntityManager */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return ArrayCollection | GraphRange[]
     */
    public function all($level = null)
    {

        $qb = $this->em->createQueryBuilder();
        $qb->select('gr')
            ->from(GraphRange::class, 'gr')
            ->where('gr.level IS NULL');

        if($level) {
            $qb->orWhere('gr.level = :level')
                ->setParameter('level', $level);
        }

        $result = $qb->getQuery()->getResult();
        return $result;
    }

}