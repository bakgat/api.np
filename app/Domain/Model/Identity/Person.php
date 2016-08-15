<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 27/06/16
 * Time: 09:33
 */

namespace App\Domain\Model\Identity;


use \DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use App\Domain\Uuid;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\AccessorOrder;
use JMS\Serializer\Annotation\Type;

/**
 * @AccessorOrder("custom", custom = {"id", "displayName" ,"email"})
 */
abstract class Person
{
    /**
     * @Groups({"student_list", "student_detail", "staff_list", "staff_detail", "group_students", "evaluation_detail"})
     *
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var Uuid id
     */
    protected $id;

    /**
     * @Groups({"student_list", "student_detail", "staff_list", "staff_detail"})
     *
     * @ORM\Column(type="string")
     * @var string
     */
    protected $firstName;

    /**
     * @Groups({"student_list", "student_detail", "staff_list", "staff_detail"})
     *
     * @ORM\Column(type="string")
     * @var string
     */
    protected $lastName;


    /**
     * @Groups({"student_list", "student_detail",  "staff_list", "staff_detail", "group_students"})
     *
     * @ORM\Column(type="string")
     * @var string
     */
    protected $email;

    /**
     * @Groups({"student_list", "student_detail", "staff_list", "staff_detail", "group_students"})
     *
     * @ORM\Column(type="gender")
     *
     * @var Gender
     */
    protected $gender;

    /**
     * @Groups({"student_list", "student_detail", "staff_list", "staff_detail"})
     * @Type("DateTime<'Y-m-d'>")
     *
     * @ORM\Column(type="date", nullable=true)
     * @var DateTime
     */
    protected $birthday;


    public function __construct($firstName, $lastName, $email, Gender $gender, DateTime $birthday = null)
    {
        $this->id = Uuid::generate(4);
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->gender = $gender;
        $this->birthday = $birthday;
    }


    /**
     * @return Uuid
     */
    public function getId()
    {
        if ($this->id instanceof Uuid) {
            return $this->id;
        }
        return Uuid::import($this->id);
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
     * @VirtualProperty
     * @Groups({"student_list", "student_detail", "staff_list", "staff_detail", "group_students", "evaluation_detail"})
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }


    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return Gender
     */
    public function getGender()
    {
        return $this->gender->getValue();
    }

    /**
     * @return DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Updating the profile for this student
     * @param $firstName
     * @param $lastName
     * @param $email
     * @param DateTime|null $birthday
     * @return Student
     */
    public function updateProfile($firstName, $lastName, $email, Gender $gender, DateTime $birthday = null)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->gender = $gender;
        $this->birthday = $birthday;
    }


}