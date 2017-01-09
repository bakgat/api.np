<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 2/01/17
 * Time: 10:51
 */

namespace App\Domain\Model\Reporting;


use App\Domain\NtUid;
use Doctrine\Common\Collections\ArrayCollection;

use JMS\Serializer\Annotation\Groups;

class ReportHeader
{
    /**
     * @Groups({"result_dto"})
     * @var ArrayCollection
     */
    private $majorHeaders;

    public function __construct()
    {
        $this->majorHeaders = new ArrayCollection;
    }

    public function intoMajor($data) {
        $id = NtUid::import($data['mId']);
        $major = $this->hasMajor($id);
        if(!$major) {
            $name = $data['mName'];
            $order = $data['mOrder'];
            $major = new MajorHeader($id, $name, $order);
            $this->majorHeaders->add($major);
        }
        return $major;
    }

    private function hasMajor($id)
    {
        $major = $this->majorHeaders->filter(function ($element) use ($id) {
            /** @var MajorHeader $element */
            return $element->getId() == $id;
        })->first();
        return $major;
    }

    /**
     * @return ArrayCollection
     */
    public function getMajorHeaders()
    {
        return clone $this->majorHeaders;
    }
}