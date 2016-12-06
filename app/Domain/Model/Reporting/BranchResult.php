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

    /**
     * @Groups({"student_iac"})
     * @var boolean
     */
    private $hasComprehensive;

    /**
     * @Groups({"student_iac"})
     * @var boolean
     */
    private $hasSpoken;

    /**
     * @Groups({"result_dto"})
     * @var ArrayCollection
     */
    private $multipleChoices;

    public function __construct(NtUid $id, $name)
    {
        $this->id = $id;
        $this->name = $name;
        $this->history = new ArrayCollection;
        $this->iacs = new ArrayCollection;
        $this->hasComprehensive = false;
        $this->hasSpoken = false;
        $this->multipleChoices = new ArrayCollection;
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

    /**
     * @param $data
     * @return McResult
     */
    public function intoMultiplechoice($data)
    {
        $id = NtUid::import($data['mcId']);
        $mc = $this->hasMC($id);
        if (!$mc) {
            $settings = $data['eSettings'];
            $selected = $data['mcSelected'];
            $mc = new McResult($id, $settings, $selected);
            $this->multipleChoices->add($mc);
        }
        return $mc;
    }

    /**
     * @param $data
     * @return $this
     */
    public function intoComprehensive($data)
    {
        if ($data['eCount'] > 0) {
            $this->hasComprehensive = true;
        }
        return $this;
    }

    /**
     * @param $data
     */
    public function intoSpoken($data)
    {
        if ($data['eCount'] > 0) {
            $this->hasSpoken = true;
        }
        return $this;
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

    public function hasMC($id)
    {
        $mc = $this->multipleChoices->filter(function ($element) use ($id) {
            /** @var McResult $element */
            return $element->getId() == $id;
        })->first();
        return $mc;
    }


    /**
     * @return ArrayCollection
     */
    public function getHistory()
    {
        return clone $this->history;
    }

    /**
     * @return ArrayCollection
     */
    public function getMultipleChoices()
    {
        return clone $this->multipleChoices;
    }

    public function hasComprehensive()
    {
        return $this->hasComprehensive;
    }

    public function hasSpoken()
    {
        return $this->hasSpoken;
    }


}