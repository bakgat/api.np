<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 21/06/16
 * Time: 08:13
 */

namespace App\Domain\Model\Identity;


use DateTime;

class StudentInGroup
{
    /** @var Student */
    protected $student;
    /** @var Group */
    protected $group;
    /** @var DateTime */
    protected $start;
    /** @var DateTime */
    protected $end;

    public function __construct(Student $student, Group $group, $start = null, $end = null)
    {
        if ($start === null) {
            $start = new DateTime();
        }
        $this->student = $student;
        $this->group = $group;
        $this->start = $start;
        $this->end = $end;
    }

    public function getStudent()
    {
        return $this->student;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function leaveGroup($end = null) {
        //end date is already taken
        //group is already left
        if($this->end !== null) {
            return $this;
        }

        if($end === null) {
            $end = new DateTime();
        }

        $this->end = $end;
        return $this;
    }
}