<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 25/06/16
 * Time: 22:54
 */

namespace App\Domain\Model\Identity;


use App\Domain\Model\Time\DateRange;
use \DateTime;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="staff")
 *
 * Class Staff
 * @package App\Domain\Model\Identity
 */
class Staff extends Person
{
    /**
     * @ORM\OneToMany(targetEntity="StaffInGroup", mappedBy="staff", cascade={"persist"})
     *
     * @var StaffInGroup[]
     */
    protected $staffInGroups;

    /**
     * @ORM\OneToMany(targetEntity="Role", mappedBy="role", , cascade={"persist"})
     *
     * @var ArrayCollection
     */
    protected $staffRoles;


    public function __construct($firstName, $lastName, $email, Gender $gender, DateTime $birthday = null)
    {
        parent::__construct($firstName, $lastName, $email, $gender, $birthday);


        $this->staffInGroups = [];
        $this->staffRoles = new ArrayCollection;
    }


    /**
     * @param Group $group
     * @param StaffType $type
     * @param DateTime|null $start
     * @param DateTime|null $end
     * @return $this
     */
    public function joinGroup(Group $group, StaffType $type, $start = null, $end = null)
    {
        if ($start == null) {
            $start = new DateTime;
        }
        $staffGroup = new StaffInGroup($this, $group, $type, ['start' => $start, 'end' => $end]);
        $this->staffInGroups[] = $staffGroup;
        return $this;
    }

    /**
     * @return Group[]
     */
    public function getGroups()
    {
        $groups = [];
        foreach ($this->staffInGroups as $staffInGroup) {
            $groups[] = $staffInGroup->getGroup();
        }
        return $groups;
    }

    /**
     * @return Group[]
     */
    public function getActiveGroups()
    {
        $groups = [];
        foreach ($this->staffInGroups as $staffInGroup) {
            if ($staffInGroup->isActive()) {
                $groups[] = $staffInGroup->getGroup();
            }
        }
        return $groups;
    }

    /**
     * @param Group $group
     * @param DateTime $date
     * @return bool
     */
    public function wasActiveInGroupAt(Group $group, DateTime $date)
    {
        foreach ($this->staffInGroups as $staffInGroup) {
            if ($staffInGroup->getGroup()->getId() == $group->getId()
                && $staffInGroup->wasActiveAt($date)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Group $group
     * @param DateRange $dateRange
     * @return bool
     */
    public function wasActiveInGroupBetween(Group $group, DateRange $dateRange)
    {
        foreach ($this->staffInGroups as $staffInGroup) {
            if ($staffInGroup->getGroup()->getId() == $group->getId()
                && $staffInGroup->wasActiveBetween($dateRange)
            ) {
                return true;
            }
        }
        return false;
    }


    public function getRoles()
    {
        $roles = new ArrayCollection;
        /** @var StaffRole $staffRole */
        foreach ($this->staffRoles as $staffRole) {
            $roles->add($staffRole->getRole());
        }
        return $roles;
    }

    public function assignRole(Role $role, $start=null,$end=null)
    {
        if($start == null) {
            $start = new DateTime;
        }
        $staffRole = new StaffRole($this, $role, ['start' => $start, 'end' => $end] );
        $this->staffRoles->add($staffRole);
        return $this;
    }
}