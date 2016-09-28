<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 20/09/16
 * Time: 14:59
 */

namespace App\Domain\Model\Events;


use App\Domain\NtUid;
use Doctrine\Common\Collections\ArrayCollection;

interface EventTrackingRepository
{
    /**
     * @param EventTracking $tracking
     * @return mixed
     */
    public function save(EventTracking $tracking);
    

    /**
     * @param $type
     * @return ArrayCollection
     */
    public function allOfType($type);

    /**
     * @param NtUid $id
     * @param $userTable
     * @return ArrayCollection
     */
    public function allOfUser(NtUid $id, $userTable);
}