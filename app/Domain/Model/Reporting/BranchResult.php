<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 3/11/16
 * Time: 10:52
 */

namespace App\Domain\Model\Reporting;


use App\Domain\Model\Time\DateRange;
use App\Domain\NtUid;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;

class BranchResult
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
     * @var ArrayCollection
     */
    private $history;

    /**
     * @Groups({"student_iac"})
     * @var ArrayCollection
     */
    private $iacs;

    public function __construct(NtUid $id, $name)
    {
        $this->id = $id;
        $this->name = $name;
        $this->history = new ArrayCollection;
        $this->iacs = new ArrayCollection;
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
     * @param $data
     * @return RangeResult
     */
    public function intoHistory($data)
    {
        $id = NtUid::import($data['grId']);
        $range = $this->hasRange($id);
        if (!$range) {
            $start = $data['grStart'];
            $end = $data['grEnd'];
            $perm = $data['prPerm'];
            $final = $data['prEnd'];
            $total = $data['prTotal'];
            $max = $data['prMax'];
            $redicodi = $data['prRedicodi'];
            $evCount = $data['prEvCount'];
            $range = new RangeResult($id, $start, $end, $perm, $final, $total, $max, $redicodi, $evCount);
            $this->history->add($range);
        }
        return $range;
    }

    public function intoIac($data)
    {
        $id = NtUid::import($data['iacId']);
        $iac = $this->hasIac($id);
        if (!$iac) {
            $start = $data['iacStart'];
            $end = $data['iacEnd'];
            $range = DateRange::fromData(['start' => $start, 'end' => $end]);
            $iac = new IacResult($id, $range);
            $this->iacs->add($iac);
        }
        return $iac;
    }

    private function hasIac($id)
    {
        $iac = $this->iacs->filter(function ($element) use ($id) {
            /** @var IacResult $element */
            return $element->getId() == $id;
        })->first();
        return $iac;
    }

    public function getIacs()
    {
        return clone $this->iacs;
    }

    public function hasRange(NtUid $id)
    {
        $range = $this->history->filter(function ($element) use ($id) {
            /** @var RangeResult $element */
            return $element->getId() == $id;
        })->first();
        return $range;
    }


    /**
     * @return ArrayCollection
     */
    public function getHistory()
    {
        return clone $this->history;
    }


}