<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 3/11/16
 * Time: 09:58
 */

namespace App\Domain\Model\Reporting;


use App\Domain\Model\Time\DateRange;
use App\Domain\NtUid;
use Doctrine\Common\Collections\ArrayCollection;


use JMS\Serializer\Annotation\Groups;

class Report
{
    /**
     * @Groups({"result_dto", "student_iac"})
     * @var DateRange
     */
    private $range;

    /**
     * @Groups({"result_dto", "student_iac"})
     * @var ArrayCollection
     */
    private $students;

    public function __construct(DateRange $dateRange)
    {
        $this->range = $dateRange;
        $this->students = new ArrayCollection;
    }

    public function intoStudent($data)
    {
        $id = NtUid::import($data['sId']);
        $stud = $this->hasStudent($id);
        if (!$stud) {
            $fn = $data['sFirstName'];
            $ln = $data['sLastName'];
            $group = isset($data['gName']) ? $data['gName'] : null;
            $stG = isset($data['stGender']) ? $data['stGender'] : null;
            $stFn = isset($data['stFirstName']) ? $data['stFirstName'] : null;
            $stLn = isset($data['stLastName']) ? $data['stLastName'] : null;
            $stud = new StudentResult($id, $fn, $ln, $group, $stFn, $stLn, $stG);
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

    /**
     * @return DateRange
     */
    public function getRange()
    {
        return $this->range;
    }

    public function sort()
    {
        /** @var StudentResult $student */
        foreach ($this->students as $student) {
            $student->sort();
        }
    }
}