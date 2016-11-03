<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 3/11/16
 * Time: 09:59
 */

namespace App\Domain\Model\Reporting;


use App\Domain\NtUid;


use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

class StudentResult
{
    /**
     * @var NtUid
     */
    private $id;

    /**
     * @Groups({"result_dto"})
     * @var string
     */
    private $firstName;

    /**
     * @Groups({"result_dto"})
     * @var string
     */
    private $lastName;

    /**
     * @Groups({"result_dto"})
     * @var ArrayCollection
     */
    private $majors;


    public function __construct(NtUid $id, $firstName, $lastName)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->majors = new ArrayCollection;
    }

    /**
     * @return NtUid
     */
    public function getId()
    {
        return $this->id;
    }

    public function intoMajor($data)
    {
        $id = NtUid::import($data['mId']);
        $maj = $this->hasMajor($id);
        if (!$maj) {
            $name =  $data['mName'];
            $maj = new MajorResult($id, $name);
            $this->majors->add($maj);
        }
        return $maj;
    }

    public function hasMajor(NtUid $id)
    {
        $maj = $this->majors->filter(function ($element) use ($id) {
            /** @var MajorResult $element */
            return $element->getId() == $id;
        })->first();
        return $maj;
    }

}