<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 25/06/16
 * Time: 23:14
 */

namespace App\Domain\Model\Identity;


class StaffInGroup extends PersonInGroup
{

    /** @var Staff */
    protected $staff;
    /** @var */
    protected $type; //TODO Make enum with types

    public function __construct(Staff $staff, Group $group, $type, $daterange)
    {
        parent::__construct($group, $daterange);
        $this->staff = $staff;
        $this->type = $type;
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