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
 * Class FlatSpokenReport
 * @package App\Domain\Model\Reporting
 */
class FlatSpokenReport
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @var NtUid
     */
    private $spId;

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
    private $gId;
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $gName;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $stFirstName;
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $stLastName;


    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $stGender;

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
     * @ORM\Column(type="integer")
     *
     * @var integer
     */
    private $eCount;
}