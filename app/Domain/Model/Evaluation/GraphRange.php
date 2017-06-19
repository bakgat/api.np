<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/06/17
 * Time: 09:28
 */

namespace App\Domain\Model\Evaluation;


use App\Domain\NtUid;

use DateTime;
use Doctrine\ORM\Mapping AS ORM;
use JMS\Serializer\Annotation\Type;

/**
 * @ORM\Entity
 * @ORM\Table(name="graph_ranges")
 *
 * Class GraphRange
 * @package App\Domain\Model\Evaluation
 */
class GraphRange
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     *
     * @var NtUid
     */
    protected $id;

    /**
     * @Type("DateTime<'Y-m-d'>")
     *
     * @ORM\Column(type="date")
     *
     * @var DateTime
     */
    protected $start;

    /**
     * @Type("DateTime<'Y-m-d'>")
     *
     * @ORM\Column(type="date")
     *
     * @var DateTime
     */
    protected $end;

    /**

     * @ORM\Column(type="guid", name="level_id")
     *
     * @var NtUid
     */
    protected $level;



    /**
     * @return NtUid
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }


    public function contains(DateTime $date) {
        return $this->start->getTimestamp() <= $date->getTimestamp() &&
            $this->end->getTimestamp() >= $date->getTimestamp();
    }
}