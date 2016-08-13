<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 13/08/16
 * Time: 07:06
 */

namespace App\Domain\Model\Identity;


use App\Domain\Uuid;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="roles")
 *
 * Class Role
 * @package App\Domain\Model\Identity
 */
class Role
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var Uuid
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\ManyToMany(targetEntity="Staff", mappedBy="roles", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var ArrayCollection
     */
    protected $staff;

    /**
     * @var ArrayCollection
     */
    protected $parents;

    public function __construct($name)
    {
        $this->id = Uuid::generate(4);
        $this->name = $name;
        $this->staff = new ArrayCollection;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function addUser($user)
    {
        if ($user instanceof Staff) {
            $this->addStaff($user);
        }
    }

    public function addStaff(Staff $staff)
    {
        $this->staff->add($staff);
    }

}