<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 3/11/16
 * Time: 06:22
 */

namespace App\Domain\Model\Reporting;

use App\Domain\NtUid;
use DateTime;
use JMS\Serializer\Annotation\Accessor;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 *
 * Class FlatHeaderReport
 * @package App\Domain\Model\Reporting
 */
class FlatHeaderReport
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var NtUid
     */
    private $hrId;


    /**
     * @ORM\Column(type="guid")
     * @var NtUid
     */
    private $gId;
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $gName;
    
    
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
    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $mOrder;


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
    private $bOrder;


    /**
     * @ORM\Column(type="guid")
     * @var NtUid
     */
    private $grId;
    /**
     * @ORM\Column(type="date")
     * @var DateTime
     */
    private $grStart;
    /**
     * @ORM\Column(type="date")
     * @var DateTime
     */
    private $grEnd;


    /**
     * @ORM\Column(type="float")
     * @var float
     */
    private $avgPermanent;
    /**
     * @ORM\Column(type="float")
     * @var float
     */
    private $avgEnd;
    /**
     * @ORM\Column(type="float")
     * @var float
     */
    private $avgTotal;
    /**
     * @ORM\Column(type="float")
     * @var float
     */
    private $prMax;

}