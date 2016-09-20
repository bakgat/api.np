<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 20/09/16
 * Time: 14:59
 */

namespace App\Domain\Model\Events;


interface EventTrackingRepository
{
    public function save(EventTracking $tracking);
}