<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 13/08/16
 * Time: 07:06
 */

namespace App\Domain\Model\Identity;


use App\Domain\NtUid;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping AS ORM;

use JMS\Serializer\Annotation\Groups;

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
     * @Groups({"role_list", "staff_roles"})
     *
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var NtUid
     */
    protected $id;

    /**
     * @Groups({"role_list", "staff_roles"})
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="StaffRole", mappedBy="role", cascade={"persist"})
     *
     * @var ArrayCollection
     */
    protected $staffRoles;

    /**
     * @var ArrayCollection
     */
    protected $parents;

    public function __construct($name)
    {
        $this->id = NtUid::generate(4);
        $this->name = $name;
        $this->staffRoles = new ArrayCollection;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
}