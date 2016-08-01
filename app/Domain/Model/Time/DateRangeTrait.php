<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 1/08/16
 * Time: 16:04
 */

namespace App\Domain\Model\Time;


trait DateRangeTrait
{
    public function isActive()
    {
        return $this->dateRange->includes(new DateTime);
    }

    public function isActiveSince()
    {
        return $this->dateRange->getStart();
    }

    public function isActiveUntil()
    {
        return $this->dateRange->getEnd();
    }
}