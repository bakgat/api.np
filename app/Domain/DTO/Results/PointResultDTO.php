<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 27/10/16
 * Time: 21:03
 */

namespace App\Domain\DTO\Results;

use App\Domain\Model\Identity\Student;
use App\Domain\NtUid;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\Type;

/**
 * @ORM\Entity
 *
 * Class PointResultDTO
 * @package App\Domain\DTO\Results
 */
class PointResultDTO
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var NtUid
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="StudentResultDTO", inversedBy="pointResults")
     *
     * @var StudentResultDTO
     */
    private $studentResult;

    /**
     * @Groups({"result_dto"})
     * @ORM\ManyToOne(targetEntity="BranchResultsDTO")
     *
     * @var BranchResultsDTO
     */
    private $branch;

    /**
     * @Groups({"result_dto"})
     * @ORM\Column(type="float")
     *
     * @var float
     */
    private $permanentScore;

    /**
     * @ORM\Column(type="float")
     *
     * @var float
     */
    private $endScore;

    /**
     * @Groups({"result_dto"})
     * @ORM\Column(type="float")
     *
     * @var float
     */
    private $totalScore;

    /**
     * @Groups({"result_dto"})
     * @ORM\Column(type="float")
     *
     * @var float
     */
    private $maxScore;

    /**
     * @Groups({"result_dto"})
     * @Type("DateTime<'Y-m-d'>")
     *
     * @ORM\Column(type="date")
     *
     * @var DateTime
     */
    private $start;

    /**
     * @Groups({"result_dto"})
     * @Type("DateTime<'Y-m-d'>")
     *
     * @ORM\Column(type="date")
     *
     * @var DateTime
     */
    private $end;



}