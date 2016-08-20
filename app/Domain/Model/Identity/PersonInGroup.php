<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 27/06/16
 * Time: 11:33
 */

namespace App\Domain\Model\Identity;


use App\Domain\Model\Time\DateRangeTrait;
use Doctrine\ORM\Mapping AS ORM;
use App\Domain\Model\Time\DateRange;
use DateTime;
use Webpatser\Uuid\Uuid;

use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Expose;

abstract class PersonInGroup
{
    use DateRangeTrait;

    /**
     * @Groups({"staff_groups", "student_groups"})
     *
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var Uuid
     */
    protected $id;


    /**
     * @Groups({"student_list", "student_detail", "staff_groups", "student_groups"})
     *
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="studentInGroups")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Group
     */
    protected $group;

    /**
     * @Groups({"student_list", "student_detail", "staff_groups", "student_groups"})
     * @Expose
     *
     * @ORM\Embedded(class="App\Domain\Model\Time\DateRange", columnPrefix=false)
     *
     * @var DateRange
     */
    protected $dateRange;

    public function __construct(Group $group, $dateRange)
    {
        $this->id = Uuid::generate(4);
        $this->group = $group;

        if ($dateRange instanceof DateRange) {
            $this->dateRange = $dateRange;
        } else {
            $this->dateRange = DateRange::fromData($dateRange);
        }
    }

    /**
     * Accessor that returns the group in this relation
     *
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }



    /**
     * Stops the relation between a student and a group at certain date.
     * If no date is provided, the current date is the end-date.
     *
     * @param DateTime|null $end
     * @return $this
     */
    public function leaveGroup($end = null)
    {
        //end date is already taken
        //group is already left
        if (!$this->isActive()) {
            return $this;
        }

        if ($end == null) {
            $now = new DateTime;
            $end = $now->modify('-1 day');
        }

        $dr = ['start' => $this->dateRange->getStart(), 'end' => $end];
        $this->dateRange = DateRange::fromData($dr);

        return $this;
    }

    public function resetStart($start )
    {
        $dr = ['start' => $start, 'end' => $this->dateRange->getEnd()];
        $this->dateRange = DateRange::fromData($dr);
        return $this;
    }

    public function block($end = null)
    {
        if($end == null) {
            $now = new DateTime;
            $end = $now->modify('-1 day');
        }

        $dr = ['start'=> $this->dateRange->getStart(), 'end' => $end];
        $this->dateRange = DateRange::fromData($dr);

        return $this;
    }
}