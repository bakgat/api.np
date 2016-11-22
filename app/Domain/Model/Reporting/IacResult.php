<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 7/11/16
 * Time: 20:46
 */

namespace App\Domain\Model\Reporting;


use App\Domain\Model\Time\DateRange;
use App\Domain\NtUid;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
class IacResult
{
    /**
     * @Groups({"student_iac"})
     * @var NtUid
     */
    private $id;
    /**
     * @Groups({"student_iac"})
     * @var DateRange
     */
    private $range;
    /**
     * @Groups({"student_iac"})
     * @var ArrayCollection
     */
    private $goals;

    /**
     * IacResult constructor.
     * @param NtUid $id
     * @param $range
     */
    public function __construct($id, $range)
    {
        $this->id = $id;
        $this->range = $range;
        $this->goals = new ArrayCollection;
    }

    /**
     * @return NtUid
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return DateRange
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * @return ArrayCollection
     */
    public function getGoals()
    {
        return clone $this->goals;
    }

    public function intoGoal($data)
    {
        $id = NtUid::import($data['igId']);
        $gId = NtUid::import($data['gId']);
        $gText = $data['gText'];
        $achieved = $data['igAchieved'];
        $practice = $data['igPractice'];
        $comment = $data['igComment'];
        $date = $data['igDate'];
        $iacGoal = new IacGoalResult($id, $gId, $gText, $achieved, $practice, $comment, $date);
        $this->goals->add($iacGoal);
        return $iacGoal;
    }

}