<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 20/09/16
 * Time: 15:01
 */

namespace App\Repositories\Events;


use App\Domain\Model\Events\EventTracking;
use App\Domain\Model\Events\EventTrackingRepository;
use App\Domain\NtUid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;

class EventTrackingDoctrineRepository implements EventTrackingRepository
{
    /** @var EntityManager */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param EventTracking $tracking
     * @return \App\Domain\NtUid
     */
    public function save(EventTracking $tracking)
    {
        $this->em->persist($tracking);
        $this->em->flush();
        return $tracking->getId();
    }

    /**
     * @param $type
     * @return ArrayCollection
     */
    public function allOfType($type)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('et.action, COUNT(et.id) as action_count, SUBSTRING(et.timestamp, 1, 10) as fd')
            ->from(EventTracking::class, 'et')
            ->where('et.actionTable=:actionTable')
            ->setParameter('actionTable', $type)
            ->groupBy('et.action, fd');
        return $qb->getQuery()->getScalarResult();
    }

    public function allOfTypeAndAction($type, $action)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('et.action, COUNT(et.id) as action_count, SUBSTRING(et.timestamp, 1, 10) as fd')
            ->from(EventTracking::class, 'et')
            ->where('et.actionTable=:actionTable')
            ->andWhere('et.action=:action')
            ->setParameter('actionTable', $type)
            ->setParameter('action', $action)
            ->groupBy('et.action, fd');
        return $qb->getQuery()->getScalarResult();
    }

    /**
     * @param NtUid $id
     * @param $userTable
     * @return ArrayCollection
     */
    public function allOfUser(NtUid $id, $userTable)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('et')
            ->from(EventTracking::class, 'et')
            ->where('et.userTable=:userTable')
            ->andWhere('et.userId=:userId')
            ->setParameter('userTable', $userTable)
            ->setParameter('userId', $id->toString());
        return $qb->getQuery()->getResult();
    }

    public function dailyReport()
    {
        $eventsSQL = "select count(id) as count, action, action_table, DATE(timestamp) as order_day from event_tracking group by action_table, action, order_day order by order_day DESC;";
        $stmt = $this->em->getConnection()->prepare($eventsSQL);
        $stmt->execute();

        $groups = [];
        foreach ($stmt->fetchAll() as $item) {

            $actionTable = camel_case($item['action_table']);
            $action = camel_case($item['action']);

            if(!isset($groups[$actionTable])) {
                $groups[$actionTable] = [];
            }
            if(!isset($groups[$actionTable][$action])) {
                $groups[$actionTable][$action] = [];
            }
            $groups[$actionTable][$action][] = [
                'date' => $item['order_day'],
                'count' => intval($item['count']),
            ];
        }
        return $groups;
    }
}
