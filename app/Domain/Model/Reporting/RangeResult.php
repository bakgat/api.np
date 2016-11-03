<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 3/11/16
 * Time: 20:21
 */

namespace App\Domain\Model\Reporting;


use App\Domain\Model\Time\DateRange;
use App\Domain\NtUid;

use JMS\Serializer\Annotation\Groups;

class RangeResult
{
    /**
     * @var NtUid
     */
    private $id;

    /**
     * @Groups({"result_dto"})
     *
     * @var DateRange
     */
    private $range;

    /**
     * @Groups({"result_dto"})
     * @var float
     */
    private $permanent;
    /**
     * @Groups({"result_dto"})
     * @var float
     */
    private $final;
    /**
     * @Groups({"result_dto"})
     * @var float
     */
    private $total;
    /**
     * @Groups({"result_dto"})
     * @var float
     */
    private $max;

    public function __construct(NtUid $id, $start, $end, $perm, $final, $total, $max)
    {
        $this->id = $id;
        $this->range = DateRange::fromData(['start' => $start, 'end' => $end]);
        $this->permanent = $perm;
        $this->final = $final;
        $this->total = $total;
        $this->max = $max;
    }

    /**
     * @return NtUid
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return DateRange
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * @return float
     */
    public function getFinal()
    {
        return round($this->final, 1);
    }

    /**
     * @return float
     */
    public function getMax()
    {
        return round($this->max, 1);
    }

    /**
     * @return float
     */
    public function getPermanent()
    {
        return round($this->permanent, 1);
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return round($this->total, 1);
    }
}