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
 * Class FlatReport
 * @package App\Domain\Model\Reporting
 */
class FlatReport
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var NtUid
     */
    private $prId;



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
    private $prPerm;
    /**
     * @ORM\Column(type="float")
     * @var float
     */
    private $prEnd;
    /**
     * @ORM\Column(type="float")
     * @var float
     */
    private $prTotal;
    /**
     * @ORM\Column(type="float")
     * @var float
     */
    private $prMax;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $prRedicodi;
    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $prEvCount;


    
}