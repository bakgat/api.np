<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 8/11/16
 * Time: 17:01
 */

namespace App\Domain\Model\Reporting;


use App\Domain\NtUid;


use JMS\Serializer\Annotation\Groups;

class GoalResult
{
    /**
     * @Groups({"student_iac"})
     * @var NtUid
     */
    private $gId;
    /**
     * @Groups({"student_iac"})
     * @var string
     */
    private $gText;

    /**
     * GoalResult constructor.
     * @param NtUid $gId
     * @param $gText
     */
    public function __construct($gId, $gText)
    {
        $this->gId = $gId;
        $this->gText = $gText;
    }
}