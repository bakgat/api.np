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
use Doctrine\ORM\EntityManager;

class EventTrackingDoctrineRepository implements EventTrackingRepository
{
    /** @var EntityManager  */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function save(EventTracking $tracking)
    {
        $this->em->persist($tracking);
        $this->em->flush();
        return $tracking->getId();
    }
}