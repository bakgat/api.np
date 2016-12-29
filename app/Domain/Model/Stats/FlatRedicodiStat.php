<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 23/12/16
 * Time: 15:16
 */

namespace App\Domain\Model\Stats;


use App\Domain\NtUid;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 *
 * Class FlatRedicodiStat
 * @package App\Domain\Model\Stats
 */
class FlatRedicodiStat
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @var NtUid
     */
    private $id;
    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $count;
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $redicodi;
}