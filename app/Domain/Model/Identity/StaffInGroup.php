<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 25/06/16
 * Time: 23:14
 */

namespace App\Domain\Model\Identity;


use Doctrine\ORM\Mapping AS ORM;

use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="staff_in_groups")
 *
 * Class StaffInGroup
 * @package App\Domain\Model\Identity
 */
class StaffInGroup extends PersonInGroup
{
    /**
     * @ORM\ManyToOne(targetEntity="Staff", inversedBy="staffInGroups")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Staff
     */
    protected $staff;
    /**
     * @Groups({"staff_groups"})
     *
     * @ORM\Column(type="stafftype")
     *
     * @var StaffType
     */
    protected $type;

    /**
     * @Groups({"staff_groups"})
     *
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="staffInGroups")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Group
     */
    protected $group;

    public function __construct(Staff $staff, Group $group, StaffType $type, $daterange)
    {
        parent::__construct($group, $daterange);
        $this->staff = $staff;
        $this->group = $group;
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
     * Accessor that returns the group in this relation
     *
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }


    /**
     *
     * @return StaffType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param StaffType $newType
     */
    public function changeType(StaffType $newType) {
        $this->type = $newType;
    }



}