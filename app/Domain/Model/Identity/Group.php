<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/06/16
 * Time: 10:35
 */

namespace App\Domain\Model\Identity;


use Doctrine\ORM\Mapping AS ORM;
use Webpatser\Uuid\Uuid;

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
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var Uuid id
     */
    protected $id;
    /**
     * @ORM\Column(type="string", unique=true)
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="StudentInGroup", mappedBy="group", cascade={"persist"})
     *
     * @var StudentInGroups[]
     */
    protected $studentInGroups;


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

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
    public function toString() {
        return $this->__toString();
    }
}