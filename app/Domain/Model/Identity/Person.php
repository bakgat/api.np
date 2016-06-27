<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 27/06/16
 * Time: 09:33
 */

namespace App\Domain\Model\Identity;


use \DateTime;
use Doctrine\ORM\Mapping AS ORM;
use Webpatser\Uuid\Uuid;

abstract class Person
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

    /**
     * @ORM\Column(type="gender")
     *
     * @var Gender
     */
    protected $gender;

    /**
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
     * @return string
     */
    public function getGender() {
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