<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 7/11/16
 * Time: 18:08
 */

namespace App\Domain\Model\Reporting;

use App\Domain\Model\Time\DateRange;
use App\Domain\NtUid;
use DateTime;
use JMS\Serializer\Annotation\Accessor;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 *
 * Class FlatIAC
 * @package app\Domain\Model\Reporting
 */
class FlatIAC
{
    /* ***************************************************
     * IAC Goals
     * **************************************************/
    /**
     * @ORM\id
     * @ORM\Column(type="guid")
     *
     * @var NtUid
     */
    private $igId;

    /* ***************************************************
     * IACs
     * **************************************************/
    /**
     * @ORM\Column(type="guid")
     * @var NtUid
     */
    private $iacId;
    /**
     * @ORM\Column(type="date")
     * @var DateTime
     */
    private $iacStart;
    /**
     * @ORM\Column(type="date")
     * @var DateTime
     */
    private $iacEnd;

    /* ***************************************************
     * Student
     * **************************************************/
    /**
     * @ORM\Column(type="guid")
     * @var NtUid
     */
    private $sId;
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $sFirstName;
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $sLastName;

    /* ***************************************************
     * GOALS
     * **************************************************/
    /**
     * @ORM\Column(type="guid")
     * @var NtUid
     */
    private $gId;
    /**
     * @ORM\Column(type="text")
     * @var string
     */
    private $gText;
    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $igAchieved;
    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $igPractice;

    /**
     * @ORM\Column(type="date")
     * @var DateTime
     */
    private $igDate;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    private $igComment;

    /* ***************************************************
     * BRANCHES
     * **************************************************/
    /**
     * @ORM\Column(type="guid")
     * @var NtUid
     */
    private $bId;
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $bName;
    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $mOrder;

    /**
     * @ORM\Column(type="guid")
     * @var NtUid
     */
    private $mId;
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $mName;


}