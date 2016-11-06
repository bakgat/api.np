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
    /**
     * @Groups({"result_dto"})
     * @var array
     */
    private $redicodi;
    /**
     * @Groups({"result_dto"})
     * @var int
     */
    private $evCount;

    public function __construct(NtUid $id, $start, $end, $perm, $final, $total, $max, $redicodi, $evCount)
    {
        $this->id = $id;
        $this->range = DateRange::fromData(['start' => $start, 'end' => $end]);
        $this->permanent = $perm;
        $this->final = $final;
        $this->total = $total;
        $this->max = $max;
        $this->redicodi = $this->cleanRedicodi($redicodi);
        $this->evCount = $evCount;
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


    private function cleanRedicodi($redicodi)
    {
        //CLEAN UP FIRST !
        $arr = explode(',', $redicodi);
        $filtered = array_filter($arr, function ($value) {
            return $value !== '';
        });

        return array_count_values($filtered);
    }

    public function getRedicodi()
    {
        return $this->redicodi;
    }

    public function getEvCount()
    {
        return $this->evCount;
    }
}