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

    public function __construct(Student $student, Group $group, $start = null, $end = null)
    {
        if ($start === null) {
            $start = new DateTime();
        }
        $this->student = $student;
        $this->group = $group;

        $dr = ['start' => $start, 'end' => $end];
        $this->dateRange = DateRange::fromData($dr);
    }

    public function getStudent()
    {
        return $this->student;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function getDateRange()
    {
        return $this->dateRange;
    }

    public function isActive()
    {
        return $this->dateRange->isFuture();
    }

    public function leaveGroup($end = null)
    {
        //end date is already taken
        //group is already left
        if(!$this->isActive()) {
            return;
        }

        if ($end === null) {
            $end = new DateTime();
        }

        $start = $this->dateRange->getStart();

        $dr = ['start' => $start, 'end' => $end];
        $this->dateRange = DateRange::fromData($dr);

        return $this;
    }
}