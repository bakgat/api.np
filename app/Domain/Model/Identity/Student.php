<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/06/16
 * Time: 10:23
 */

namespace App\Domain\Model\Identity;

use DateTime;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Webpatser\Uuid\Uuid;

//* @ORM\Entity
//* @ORM\Table(name="students")

/**
 *
 * Class Student
 * @package App\Domain\Model\Person
 */
class Student
{
    /** @var Uuid id */
    protected $id;

    protected $firstName;

    protected $lastName;

    protected $email;

    protected $gender;

    /** @var DateTime */
    protected $birthday;

    /** @var StudentInGroup[] */
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
     * @return
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
        $studentGroup = new StudentInGroup($this, $group, $start, $end);
        $this->studentInGroups[] = $studentGroup;
        return $this;
    }

    public function leaveGroup(Group $group, $end = null)
    {
        $id = $group->getId();
        foreach ($this->studentInGroups as $studentInGroup) {
            if($studentInGroup->getGroup()->getId() === $id) {
                $studentInGroup->leaveGroup($end);
            }
        }
    }

    public function groups()
    {
        $groups = [];
        foreach ($this->studentInGroups as $studentInGroup) {
            $groups[] = $studentInGroup->getGroup();
        }
        return $groups;
    }

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

}