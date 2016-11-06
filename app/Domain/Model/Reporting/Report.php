<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 3/11/16
 * Time: 09:58
 */

namespace App\Domain\Model\Reporting;


use App\Domain\NtUid;
use Doctrine\Common\Collections\ArrayCollection;


use JMS\Serializer\Annotation\Groups;

class Report
{
    private $range;

    /**
     * @Groups({"result_dto"})
     * @var ArrayCollection
     */
    private $students;

    public function __construct()
    {
        $this->students = new ArrayCollection;
    }

    public function intoStudent($data)
    {
        $id = NtUid::import($data['sId']);
        $stud = $this->hasStudent($id);
        if (!$stud) {
            $fn = $data['sFirstName'];
            $ln = $data['sLastName'];
            $group = $data['gName'];
            $stFn = $data['stFirstName'];
            $stLn = $data['stLastName'];
            $stud = new StudentResult($id, $fn, $ln, $group, $stFn, $stLn);
            $this->students->add($stud);
        }
        return $stud;
    }

    public function hasStudent(NtUid $id)
    {
        $stud = $this->students->filter(function ($element) use ($id) {
            /** @var StudentResult $element */
            return $element->getId() == $id;
        })->first();
        return $stud;
    }

    public function getStudentResults()
    {
        return clone $this->students;
    }
}