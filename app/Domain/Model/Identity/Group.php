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

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="groups")
 *
 * @ExclusionPolicy("all")
 *
 * Class Group
 * @package App\Domain\Model\Identity
 */
class Group
{
    /**
     * @Groups({"group", "group_students"})
     * @Expose
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var Uuid id
     */
    protected $id;
    /**
     * @Groups({"group", "group_students"})
     * @Expose
     * @ORM\Column(type="string", unique=true)
     *
     * @var string
     */
    protected $name;

    /**
     * @Groups({"group_students"})
     * @Expose
     * @ORM\OneToMany(targetEntity="StudentInGroup", mappedBy="group", cascade={"persist"})
     *
     * @var StudentInGroup[]
     */
    protected $studentInGroups;

    /**
     * @ORM\OneToMany(targetEntity="App\Domain\Model\Education\BranchForGroup", mappedBy="group", cascade={"persist"})
     *
     * @var BranchForGroup[]
     */
    protected $branchForGroups;


    public function __construct($name)
    {
        $this->id = Uuid::generate(4);
        $this->name = $name;

    }

    public function getId()
    {
        if ($this->id instanceof Uuid) {
            return $this->id;
        }
        return Uuid::import($this->id);
    }

    public function getName()
    {
        return $this->name;
    }

    public function updateName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    public function toString()
    {
        return $this->__toString();
    }



}