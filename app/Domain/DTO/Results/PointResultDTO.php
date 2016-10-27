<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 27/10/16
 * Time: 21:03
 */

namespace App\Domain\DTO\Results;

use App\Domain\NtUid;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

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
     * @ORM\ManyToOne(targetEntity="BranchResultsDTO", inversedBy="pointResults")
     *
     * @var BranchResultsDTO
     */
    private $branchResult;

    /**
     * @ORM\Column(type="float")
     *
     * @var float
     */
    private $permanentScore;

    /**
     *
     * @var float
     */
    private $endScore;

    /**
     *
     * @var float
     */
    private $totalScore;

    /**
     *
     * @var float
     */
    private $maxScore;

    /**
     * @var DateTime
     */
    private $start;

    /**
     *
     * @var DateTime
     */
    private $end;

}