<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 21/06/16
 * Time: 08:13
 */

namespace App\Domain\Model\Identity;


use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="student_in_groups")
 *
 *
 * Class StudentInGroup
 * @package App\Domain\Model\Identity
 */
class StudentInGroup extends PersonInGroup
{
    /**
     * @Groups({"group_students"})
     *
     * @ORM\ManyToOne(targetEntity="Student", inversedBy="studentInGroups")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Student
     */
    protected $student;

    /**
     * @Groups({"student_list", "student_groups"})
     *
     * @ORM\Column(type="smallint", nullable=true)
     *
     * @var int
     */
    protected $number;

    /**
     * @Groups({"student_list", "student_groups"})
     *
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="studentInGroups")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Group
     */
    protected $group;

    public function __construct(Student $student, Group $group, $daterange)
    {
        parent::__construct($group, $daterange);
        $this->group = $group;
        $this->student = $student;
    }

    /**
     * Accessor that returns the student in this relation
     *
     * @return Student
     */
    public function getStudent()
    {
        return $this->student;
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
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }


}