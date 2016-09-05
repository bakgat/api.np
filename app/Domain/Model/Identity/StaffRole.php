<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 13/08/16
 * Time: 21:08
 */

namespace App\Domain\Model\Identity;

use App\Domain\Model\Time\DateRange;
use App\Domain\Model\Time\DateRangeTrait;
use App\Domain\Uuid;

use DateTime;
use Doctrine\ORM\Mapping AS ORM;

use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="staff_roles")
 * Class StaffRole
 * @package App\Domain\Model\Identity
 */
class StaffRole
{
    use DateRangeTrait;

    /**
     * @Groups({"staff_roles"})
     *
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var Uuid
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Staff", inversedBy="staffRoles")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Staff
     */
    protected $staff;

    /**
     * @Groups({"staff_roles"})
     *
     * @ORM\ManyToOne(targetEntity="Role", inversedBy="staffRoles")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Role
     */
    protected $role;

    /**
     * @Groups({"staff_roles"})
     *
     * @ORM\Embedded(class="App\Domain\Model\Time\DateRange", columnPrefix=false)
     *
     * @var DateRange
     */
    protected $dateRange;

    public function __construct(Staff $staff, Role $role, $dateRange)
    {
        $this->id = Uuid::generate(4);
        $this->staff = $staff;
        $this->role = $role;
        $this->dateRange = DateRange::fromData($dateRange);

    }

    public function getId()
    {
        return $this->id;
    }

    public function getStaff()
    {
        return $this->staff;
    }

    public function getRole()
    {
        return $this->role;
    }
}