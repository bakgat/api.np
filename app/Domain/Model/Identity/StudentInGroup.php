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
 * @ExclusionPolicy("all")
 *
 * Class StudentInGroup
 * @package App\Domain\Model\Identity
 */
class StudentInGroup extends PersonInGroup
{
    /**
     * @Groups({"group_students"})
     * @Expose
     *
     * @ORM\ManyToOne(targetEntity="Student", inversedBy="studentInGroups")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Student
     */
    protected $student;

    public function __construct(Student $student, Group $group, $daterange)
    {
        parent::__construct($group, $daterange);
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
     * @return string
     */
    public function __toString()
    {
        return $this->student->getDisplayName() . ' - ' . $this->group->getName()
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