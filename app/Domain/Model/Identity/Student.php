<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/06/16
 * Time: 10:23
 */

namespace App\Domain\Model\Identity;

use App\Domain\Model\Evaluation\RedicodiForStudent;
use App\Domain\Model\Time\DateRange;
use \DateTime;
use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\AccessorOrder;
use JMS\Serializer\Annotation\VirtualProperty;

/**
 * @AccessorOrder("custom", custom = {"id", "displayName", "firstName", "lastName", "schoolId", "gender", "birthday", "studentInGroups"})
 *
 * @ORM\Entity
 * @ORM\Table(name="students")
 *
 * Class Student
 * @package App\Domain\Model\Person
 */
class Student extends Person
{
    /**
     * @Groups({"student_detail"})
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $schoolId;

    /**
     * @Groups({"student_detail"})
     *
     * @ORM\OneToMany(targetEntity="StudentInGroup", mappedBy="student", cascade={"persist"})
     *
     * @var StudentInGroup[]
     */
    protected $studentInGroups;

    /**
     * @ORM\OneToMany(targetEntity="App\Domain\Model\Evaluation\RedicodiForStudent", mappedBy="student", cascade={"persist"})
     *
     * @var RedicodiForStudent[]
     */
    protected $redicodiForStudents;


    public function __construct($firstName, $lastName, $schoolId, Gender $gender, DateTime $birthday = null)
    {
        parent::__construct($firstName, $lastName, $gender, $birthday);
        $this->schoolId = $schoolId;

        $this->studentInGroups = [];
    }

    /**
     * @return string
     */
    public function getSchoolId()
    {
        return $this->schoolId;
    }
    /**
     *
     * @param $group
     * @param DateTime $start
     * @param DateTime $end
     * @return $this
     */
    public function joinGroup(Group $group, $start = null, $end = null)
    {
        if ($start == null) {
            $start = new DateTime;
        }
        $studentGroup = new StudentInGroup($this, $group, ['start' => $start, 'end' => $end]);
        $this->studentInGroups[] = $studentGroup;
        return $this;
    }

    /**
     * @param Group $group
     * @param DateTime|null $end
     */
    public function leaveGroup(Group $group, $end = null)
    {
        $id = $group->getId();
        foreach ($this->studentInGroups as $studentInGroup) {
            if ($studentInGroup->getGroup()->getId() == $id) {
                $studentInGroup->leaveGroup($end);
            }
        }
    }

    /**
     * @return Group[]
     */
    public function getGroups()
    {
        $groups = [];
        foreach ($this->studentInGroups as $studentInGroup) {
            $groups[] = $studentInGroup->getGroup();
        }
        return $groups;
    }

    /**
     * @VirtualProperty
     * @Groups({"student_list", "student_detail"})
     *
     * @return Group[]
     */
    public function getActiveGroups()
    {
        $groups = [];
        foreach ($this->studentInGroups as $studentInGroup) {
            if ($studentInGroup->isActive()) {
                $groups[] = $studentInGroup->getGroup();
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
        foreach ($this->studentInGroups as $studentInGroup) {
            if ($studentInGroup->getGroup()->getId() == $group->getId()
                && $studentInGroup->wasActiveAt($date)
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
        foreach ($this->studentInGroups as $studentInGroup) {
            if ($studentInGroup->getGroup()->getId() == $group->getId()
                && $studentInGroup->wasActiveBetween($dateRange)
            ) {
                return true;
            }
        }
        return false;
    }
}