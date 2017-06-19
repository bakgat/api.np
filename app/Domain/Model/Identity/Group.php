<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/06/16
 * Time: 10:35
 */

namespace App\Domain\Model\Identity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use App\Domain\NtUid;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;

/**
 * @ORM\Entity
 * @ORM\Table(name="groups")
 *
 * Class Group
 * @package App\Domain\Model\Identity
 */
class Group
{
    /**
     * @Groups({"group", "group_students", "student_list", "student_detail", "staff_groups", "student_groups"})
     *
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var NtUid id
     */
    protected $id;
    /**
     * @Groups({"group", "group_students", "student_list", "student_detail", "staff_groups", "student_groups"})
     *
     * @ORM\Column(type="string", unique=true)
     *
     * @var string
     */
    protected $name;

    /**
     * @Groups({"group"})
     *
     * @ORM\Column(type="boolean", options={"default" : true})
     *
     * @var bool
     */
    protected $active;

    /**
     * @Groups({"group_students"})
     *
     * @ORM\OneToMany(targetEntity="StudentInGroup", mappedBy="group", cascade={"persist"})
     *
     * @var ArrayCollection
     */
    protected $studentInGroups;

    /**
     * @Groups({"group_staff"})
     *
     * @ORM\OneToMany(targetEntity="StaffInGroup", mappedBy="group", cascade={"persist"})
     *
     * @var ArrayCollection
     */
    protected $staffInGroups;

    /**
     * @ORM\OneToMany(targetEntity="App\Domain\Model\Education\BranchForGroup", mappedBy="group", cascade={"persist"})
     *
     * @var ArrayCollection
     */
    protected $branchForGroups;

    /**
     * @ORM\Column(type="guid", name="level_id")
     *
     * @var NtUid
     */
    protected $level;


    public function __construct($name, $active = true)
    {
        $this->id = NtUid::generate(4);
        $this->name = $name;
        $this->active = $active;
        $this->studentInGroups = new ArrayCollection;
        $this->branchForGroups = new ArrayCollection;
    }

    public function getId()
    {
        if ($this->id instanceof NtUid) {
            return $this->id;
        }
        return NtUid::import($this->id);
    }

    public function getName()
    {
        return $this->name;
    }

    public function updateName($name)
    {
        $this->name = $name;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function activate()
    {
        $this->active = true;
    }

    public function block()
    {
        $this->active = false;
    }


    public function getStudentInGroups()
    {
        return clone $this->studentInGroups;
    }

    /**
     * @return ArrayCollection
     */
    public function getStaffInGroups()
    {
        return clone $this->staffInGroups;
    }

    public function getBranchForGroups()
    {
        return clone $this->branchForGroups;
    }

    public function getLevel()
    {
        return $this->level;
    }
}