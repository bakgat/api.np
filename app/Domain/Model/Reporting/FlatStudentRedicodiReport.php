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
 * Class FlatStudentRedicodiReport
 * @package App\Domain\Model\Reporting
 */
class FlatStudentRedicodiReport
{
    /**
     * @ORM\Id
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
     * @ORM\Column(type="string")
     * @var string
     */
    private $rfsRedicodi;
}