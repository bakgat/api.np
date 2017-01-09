<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 2/01/17
 * Time: 21:06
 */

namespace App\Domain\Model\Reporting;


use App\Domain\NtUid;
use JMS\Serializer\Annotation\Groups;

class BranchHeader
{
    /**
     * @var NtUid
     */
    private $id;

    /**
     * @Groups({"result_dto", "student_iac"})
     * @var string
     */
    private $name;
    /**
     * @Groups({"result_dto"})
     * @var float
     */
    private $avgPermanent;
    /**
     * @Groups({"result_dto"})
     * @var float
     */
    private $avgEnd;
    /**
     * @Groups({"result_dto"})
     * @var float
     */
    private $avgTotal;
    /**
     * @Groups({"result_dto"})
     * @var float
     */
    private $max;
    /**
     * @var MajorHeader
     */
    private $major;
    private $order;

    public function __construct(NtUid $id, $name, $order,$avgPermanent, $avgEnd, $avgTotal, $max)
    {
        $this->id = $id;
        $this->name = $name;
        $this->avgPermanent = $avgPermanent;
        $this->avgEnd = $avgEnd;
        $this->avgTotal = $avgTotal;
        $this->max = $max;
        $this->order = $order;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getAvgPermanent()
    {
        return $this->avgPermanent;
    }

    /**
     * @return float
     */
    public function getAvgEnd()
    {
        return $this->avgEnd;
    }

    /**
     * @return float
     */
    public function getAvgTotal()
    {
        return $this->avgTotal;
    }

    /**
     * @return float
     */
    public function getMax()
    {
        return $this->max;
    }


}