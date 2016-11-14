<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 12/11/16
 * Time: 20:38
 */

namespace App\Domain\Services;


use App\Domain\Model\Events\EventTracking;
use App\Domain\Model\Events\EventTrackingRepository;

class TrackService
{
    /**
     * @var EventTrackingRepository
     */
    private $trackRepo;

    public function __construct(EventTrackingRepository $eventTrackingRepository)
    {
        $this->trackRepo = $eventTrackingRepository;
    }

    public function track($authId, $table, $action, $id)
    {
        $track = new EventTracking('staff', $authId, $table, $action, $id);
        $this->trackRepo->save($track);
        return true;
    }
}