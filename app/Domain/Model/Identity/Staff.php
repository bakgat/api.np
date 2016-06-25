<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 25/06/16
 * Time: 22:54
 */

namespace App\Domain\Model\Identity;


use DateTime;
use Webpatser\Uuid\Uuid;

class Staff
{
    /** @var Uuid */
    protected $id;
    /** @var string */
    protected $firstName;
    /** @var string */
    protected $lastName;
    /** @var string */
    protected $email;

    protected $gender;

    /** @var DateTime */
    protected $birthday;

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
    public function getDisplayName() {
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
     * @return DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }
}