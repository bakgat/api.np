<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/06/16
 * Time: 10:23
 */

namespace App\Domain\Model\Identity;

use App\Domain\Model\Time\DateRange;
use DateTime;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Webpatser\Uuid\Uuid;

//
//*

/**
 * @ORM\Entity
 * @ORM\Table(name="students")
 *
 * Class Student
 * @package App\Domain\Model\Person
 */
class Student
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @var Uuid id
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $lastName;


    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $email;


    protected $gender;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @var DateTime
     */
    protected $birthday;

    /**
     * @ORM\OneToMany(targetEntity="StudentInGroup", mappedBy="student", cascade={"persist"})
     *
     * @var StudentInGroup[]
     */
    protected $studentInGroups;


    public function __construct($firstName, $lastName, $email)
    {
        $this->id = Uuid::generate(4);
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
    }

    /**
     * @return Uuid
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }


    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param DateTime $birthday
     * @return $this
     */
    public function setBirthday(DateTime $birthday)
    {
        $this->birthday = $birthday;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
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
            if ($studentInGroup->getGroup()->getId() === $id) {
                $studentInGroup->leaveGroup($end);
            }
        }
    }

    /**
     * @return Group[]
     */
    public function groups()
    {
        $groups = [];
        foreach ($this->studentInGroups as $studentInGroup) {
            $groups[] = $studentInGroup->getGroup();
        }
        return $groups;
    }

    /**
     * @return Group[]
     */
    public function activeGroups()
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