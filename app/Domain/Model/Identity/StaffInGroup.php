<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 25/06/16
 * Time: 23:14
 */

namespace App\Domain\Model\Identity;


use App\Domain\Model\Time\DateRange;
use DateTime;
use Webpatser\Uuid\Uuid;

class StaffInGroup
{
    /** @var Uuid */
    protected $id;
    /** @var Staff */
    protected $staff;
    /** @var Group */
    protected $group;
    /** @var */
    protected $type; //TODO Make enum with types
    /** @var DateRange */
    protected $dateRange;


    public function __construct(Staff $staff, Group $group, $type, $daterange)
    {
        $this->id = Uuid::generate(4);
        $this->staff = $staff;
        $this->group = $group;
        $this->type = $type;
        if ($daterange instanceof DateRange) {
            $this->dateRange = $daterange;
        } else {
            $this->dateRange = DateRange::fromData($daterange);
        }
    }

    /**
     * @return Staff
     */
    public function getStaff()
    {
        return $this->staff;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

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
        return $this->dateRange->getEnd() >= new DateTime
        && $this->dateRange->getStart() <= new DateTime;
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

    /**
     * Stops the relation between a student and a group at certain date.
     * If no date is provided, the current date is the end-date.
     *
     * @param DateTime|null $end
     * @return $this
     */
    public function leaveGroup($end = null)
    {
        //end date is already taken
        //group is already left
        if (!$this->isActive()) {
            return $this;
        }

        if ($end == null) {
            $end = new DateTime;
        }
        $dr = ['start' => $this->dateRange->getStart(), 'end' => $end];
        $this->dateRange = DateRange::fromData($dr);

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->staff->getDisplayName() . ' - ' . $this->group->getName()
        . ': ' . $this->dateRange;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->__toString();
    }
}