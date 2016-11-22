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
    private $id;
    /**
     * @Groups({"student_iac"})
     * @var string
     */
    private $text;

    /**
     * GoalResult constructor.
     * @param NtUid $gId
     * @param $gText
     */
    public function __construct($gId, $gText)
    {
        $this->id = $gId;
        $this->text = $gText;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
}