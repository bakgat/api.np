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

    /**
     * @Groups({"result_dto"})
     * @var string
     */
    private $group;

    /**
     * @Groups({"result_dto"})
     * @var string
     */
    private $titularFirstName;

    /**
     * @Groups({"result_dto"})
     * @var string
     */
    private $titularLastName;


    public function __construct(NtUid $id, $firstName, $lastName, $groupName, $stFirstName, $stLastName)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->group = $groupName;
        $this->titularFirstName = $stFirstName;
        $this->titularLastName = $stLastName;
        $this->majors = new ArrayCollection;
    }

    /**
     * @return NtUid
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return string
     */
    public function getTitular()
    {
        return $this->titularFirstName . ' ' . $this->titularLastName;
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



    public function getMajorResults()
    {
        return clone $this->majors;
    }


}