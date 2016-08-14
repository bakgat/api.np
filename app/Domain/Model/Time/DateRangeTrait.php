<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 1/08/16
 * Time: 16:04
 */

namespace App\Domain\Model\Time;


use DateTime;

trait DateRangeTrait
{
    /**
     * Returns true if this relation is active.
     *
     * That means that there is an infinite end-date or
     * the current date is included in the daterange
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->dateRange->includes(new DateTime);
    }

    /**
     * Returns the start date of this relation
     *
     * @return DateTime
     */
    public function isActiveSince()
    {
        return $this->dateRange->getStart();
    }

    /**
     * Returns the end date of this relation
     *
     * @return DateTime
     */
    public function isActiveUntil()
    {
        return $this->dateRange->getEnd();
    }

    /**
     * Returns true if this relation was active at a certain date.
     *
     * That means that the given date is included in the date range.
     *
     * @param DateTime $date
     * @return bool
     */
    public function wasActiveAt(DateTime $date)
    {
        return $this->dateRange->includes($date);
    }

    /**
     * Returns true if this relation was active between a certain date range.
     *
     * That means that the given daterange is completely included in the date range.
     * @param DateRange $dateRange
     * @return bool
     */
    public function wasActiveBetween(DateRange $dateRange)
    {
        return $this->dateRange->includes($dateRange);
    }
}