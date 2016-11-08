<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 7/11/16
 * Time: 21:15
 */

namespace App\Domain\Model\Reporting;


use App\Domain\NtUid;

use DateTime;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;

class IacGoalResult
{
    /**
     * @var NtUid
     */
    private $id;
    /**
     * @var NtUid
     */
    private $gId;
    /**
     * @Groups({"student_iac"})
     * @var string
     */
    private $text;
    /**
     * @Groups({"student_iac"})
     * @var boolean
     */
    private $achieved;
    /**
     * @Groups({"student_iac"})
     * @var boolean
     */
    private $practice;
    /**
     * @Groups({"student_iac"})
     * @var string
     */
    private $comment;

    /**
     * @Groups({"student_iac"})
     * @Type("DateTime<'Y-m-d'>")
     *
     * @var DateTime
     */
    private $date;


    /**
     * IacGoalResult constructor.
     * @param NtUid $id
     * @param NtUid $gId
     * @param $gText
     * @param $achieved
     * @param $practice
     * @param $comment
     */
    public function __construct($id, $gId, $gText, $achieved, $practice, $comment, $date)
    {
        $this->id = $id;
        $this->gId = $gId;
        $this->text = $gText;
        $this->achieved = $achieved == null ? false : $achieved;
        $this->practice = $practice == null ? false : $practice;
        $this->comment = $comment == null ? '' : $comment;
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return boolean
     */
    public function isAchieved()
    {
        return $this->achieved;
    }

    /**
     * @return boolean
     */
    public function isPractice()
    {
        return $this->practice;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}