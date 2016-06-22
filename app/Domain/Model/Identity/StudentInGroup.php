<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 21/06/16
 * Time: 08:13
 */

namespace App\Domain\Model\Identity;


use App\Domain\Model\Time\DateRange;
use DateTime;

class StudentInGroup
{
    /** @var Student */
    protected $student;
    /** @var Group */
    protected $group;
    /** @var DateRange */
    protected $dateRange;

    public function __construct(Student $student, Group $group, $daterange)
    {
        $this->student = $student;
        $this->group = $group;

        if ($daterange instanceof DateRange) {
            $this->dateRange = $daterange;
        } else {
            $this->dateRange = DateRange::fromData($daterange);
        }
    }

    public function getStudent()
    {
        return $this->student;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function isActive()
    {
        return $this->dateRange->isFuture();
    }

    public function isActiveSince()
    {
        return $this->dateRange->getStart();
    }

    public function isActiveUntil()
    {
        return $this->dateRange->getEnd();
    }

    public function wasActiveAt(DateTime $date)
    {
        return $this->dateRange->includes($date);
    }

    public function wasActiveBetween(DateRange $dateRange)
    {
        return $this->dateRange->includes($dateRange);
    }

    public function leaveGroup($end = null)
    {
        //end date is already taken
        //group is already left
        if (!$this->isActive()) {
            return;
        }

        if ($end === null) {
            $end = new DateTime();
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
        return $this->student->getDisplayName() . ' - ' . $this->group->getName()
        . ': ' . $this->dateRange;
    }
}