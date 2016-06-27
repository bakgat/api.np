<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 27/06/16
 * Time: 11:33
 */

namespace App\Domain\Model\Identity;


use Doctrine\ORM\Mapping AS ORM;
use App\Domain\Model\Time\DateRange;
use DateTime;
use Webpatser\Uuid\Uuid;

abstract class PersonInGroup
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var Uuid
     */
    protected $id;


    /**
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="studentInGroups")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Group
     */
    protected $group;

    /**
     * @ORM\Embedded(class="App\Domain\Model\Time\DateRange", columnPrefix=false)
     *
     * @var DateRange
     */
    protected $dateRange;

    public function __construct(Group $group, $daterange)
    {
        $this->id = Uuid::generate(4);
        $this->group = $group;

        if ($daterange instanceof DateRange) {
            $this->dateRange = $daterange;
        } else {
            $this->dateRange = DateRange::fromData($daterange);
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
     * Returns true if this relation is active.
     *
     * That means that there is an infinite end-date or
     * the current date is included in the daterange
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->dateRange->getEnd() >= new DateTime
        && $this->dateRange->getStart() <= new DateTime;
    }

    /**
     * Returns the start date of this relation
     *
     * @return DateTime
     */
    public function isActiveSince()
    {
        return $this->dateRange->getStart();
    }

    /**
     * Returns the end date of this relation
     *
     * @return DateTime
     */
    public function isActiveUntil()
    {
        return $this->dateRange->getEnd();
    }

    /**
     * Returns true if this relation was active at a certain date.
     *
     * That means that the given date is included in the date range.
     *
     * @param DateTime $date
     * @return bool
     */
    public function wasActiveAt(DateTime $date)
    {
        return $this->dateRange->includes($date);
    }

    /**
     * Returns true if this relation was active between a certain date range.
     *
     * That means that the given daterange is completely included in the date range.
     * @param DateRange $dateRange
     * @return bool
     */
    public function wasActiveBetween(DateRange $dateRange)
    {
        return $this->dateRange->includes($dateRange);
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


}