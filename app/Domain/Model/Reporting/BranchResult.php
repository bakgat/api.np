<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 3/11/16
 * Time: 10:52
 */

namespace App\Domain\Model\Reporting;


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
     * @Groups({"result_dto"})
     * @var string
     */
    private $name;

    /**
     * @Groups({"result_dto"})
     * @var ArrayCollection
     */
    private $history;

    public function __construct(NtUid $id, $name)
    {
        $this->id = $id;
        $this->name = $name;
        $this->history = new ArrayCollection;
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
            $range = new RangeResult($id, $start, $end, $perm, $final, $total, $max);
            $this->history->add($range);
        }
        return $range;
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