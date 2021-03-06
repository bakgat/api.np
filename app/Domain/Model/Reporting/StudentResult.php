<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 3/11/16
 * Time: 09:59
 */

namespace App\Domain\Model\Reporting;


use App\Domain\Model\Time\DateRange;
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
     * @Groups({"result_dto", "student_iac"})
     * @var string
     */
    private $firstName;

    /**
     * @Groups({"result_dto", "student_iac"})
     * @var string
     */
    private $lastName;

    /**
     * @Groups({"result_dto", "student_iac"})
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

    /**
     * @Groups({"result_dto"})
     * @var string
     */
    private $titularGender;

    /**
     * @Groups({"result_dto"})
     * @var string
     */
    private $feedback;

    /**
     * @Groups({"result_dto"})
     * @var ArrayCollection
     */
    private $redicodi;


    public function __construct(NtUid $id, $firstName, $lastName, $groupName, $stFirstName, $stLastName, $stGender)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->group = $groupName;
        $this->titularGender = $stGender;
        $this->titularFirstName = $stFirstName;
        $this->titularLastName = $stLastName;
        $this->majors = new ArrayCollection;
        $this->iacs = new ArrayCollection;
        $this->redicodi = new ArrayCollection;
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
    public function getTitularFirstName()
    {
        return $this->titularFirstName;
    }

    /**
     * @return string
     */
    public function getTitularLastName()
    {
        return $this->titularLastName;
    }

    /**
     * @return string
     */
    public function getTitular()
    {
        return $this->titularFirstName . ' ' . $this->titularLastName;
    }

    /**
     * @return mixed
     */
    public function getTitularGender()
    {
        return $this->titularGender;
    }


    /**
     * @return string
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    public function intoMajor($data)
    {
        $id = NtUid::import($data['mId']);

        $maj = $this->hasMajor($id);
        if (!$maj) {
            $name = $data['mName'];
            $order = isset($data['mOrder']) ? $data['mOrder'] : null;
            $maj = new MajorResult($id, $name, $order);
            $this->majors->add($maj);
        }
        return $maj;
    }

    private function hasMajor(NtUid $id)
    {
        $maj = $this->majors->filter(function ($element) use ($id) {
            /** @var MajorResult $element */
            return $element->getId() == $id;
        })->first();
        return $maj;
    }

    public function intoFeedback($data)
    {
        $this->feedback = $data['frSummary'];
        return $this;
    }


    public function getMajorResults()
    {
        return clone $this->majors;
    }


    public function intoRedicodi($data)
    {
        if (!$this->redicodi->contains($data['rfsRedicodi'])) {
            $this->redicodi->add($data['rfsRedicodi']);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getRedicodi()
    {
        return $this->redicodi->getValues();
    }

    public function sort()
    {
        $iterator = $this->majors->getIterator();
        while ($iterator->valid()) {
            $iterator->current()->sort();
            $iterator->next();
        }
        $iterator->rewind();
        /**
         * @var MajorResult $a
         * @var MajorResult $b
         */
        $iterator->uasort(function ($a, $b) {
            /**
             * @var MajorResult $a
             * @var MajorResult $b
             */
            return ($a->getOrder() < $b->getOrder()) ? -1 : 1;
        });
        $this->majors = new ArrayCollection(iterator_to_array($iterator));
        return $this->majors;
    }


}