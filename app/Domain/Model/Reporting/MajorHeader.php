<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 2/01/17
 * Time: 21:06
 */

namespace App\Domain\Model\Reporting;


use App\Domain\NtUid;
use Doctrine\Common\Collections\ArrayCollection;

use JMS\Serializer\Annotation\Groups;

class MajorHeader
{
    /**
     * @var NtUid
     */
    private $id;

    /**
     * @Groups({"result_dto"})
     * @var string
     */
    private $name;
    /**
     * @Groups({"result_dto"})
     * @var ArrayCollection
     */
    private $branchHeaders;

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
     * @var int
     */
    private $order;

    public function __construct(NtUid $id, $name, $order)
    {
        $this->id = $id;
        $this->branchHeaders = new ArrayCollection;
        $this->name = $name;
        $this->order = $order;
    }

    public function intoBranch($data) {
        $id = NtUid::import($data['bId']);
        $branch = $this->hasBranch($id);
        if(!$branch) {
            $name = $data['bName'];
            $order = $data['bOrder'];
            $avgP = $data['avgPermanent'];
            $avgE = $data['avgEnd'];
            $avgT = $data['avgTotal'];
            $max = $data['prMax'];
            $branch = new BranchHeader($id, $name, $order,$avgP, $avgE, $avgT, $max);
            $this->addAvgPermanent($avgP);
            $this->addAvgEnd($avgE);
            $this->addAvgTotal($avgT);
            $this->addMax($max);
            $this->branchHeaders->add($branch);
        }
        return $branch;
    }
    public function hasBranch($id) {
        $branch = $this->branchHeaders->filter(function ($element) use ($id) {
            /** @var BranchHeader $element */
            return $element->getId() == $id;
        })->first();
        return $branch;
    }

    public function addAvgPermanent($p) {
        $this->avgPermanent += $p;
    }
    public function addAvgEnd($p) {
        $this->avgEnd += $p;
    }
    public function addAvgTotal($p) {
        $this->avgTotal += $p;
    }
    public function addMax($p) {
        $this->max += $p;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ArrayCollection
     */
    public function getBranchHeaders()
    {
        return clone $this->branchHeaders;
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