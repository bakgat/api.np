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
     * @Groups({"staff_list", "staff_detail"})
     *
     * @ORM\ManyToOne(targetEntity="Role", inversedBy="staffRoles")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Role
     */
    protected $role;

    /**
     * @Groups({"staff_list", "staff_detail"})
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

        if ($dateRange instanceof DateRange) {
            $this->dateRange = $dateRange;
        } else {
            $this->dateRange = DateRange::fromData($dateRange);
        }
    }

    public function getStaff()
    {
        return $this->staff;
    }

    public function getRole()
    {
        return $this->role;
    }


    public function block($end = null)
    {
        if(!$this->isActive()) {
            return $this;
        }

        if($end == null) {
            $now = new DateTime;
            $end = $now->modify('-1 day');
        }

        $dr = ['start'=> $this->dateRange->getStart(), 'end' => $end];
        $this->dateRange = DateRange::fromData($dr);

        return $this;
    }

}