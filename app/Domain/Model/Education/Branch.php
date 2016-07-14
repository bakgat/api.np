<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 30/06/16
 * Time: 16:01
 */

namespace App\Domain\Model\Education;


use Webpatser\Uuid\Uuid;

class Branch
{
    /** @var Uuid */
    private $id;

    /** @var string */
    private $name;

    /** @var Major */
    private $major;

    public function __construct($name)
    {
        $this->id = Uuid::generate(4);
        $this->name = $name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function joinMajor(Major $major)
    {
        $this->major = $major;
    }

    public function getMajor()
    {
        return $this->major;
    }

    public function changeName($name)
    {
        $this->name = $name;
    }
}