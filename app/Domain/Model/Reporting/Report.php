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


use Exception;
use JMS\Serializer\Annotation\Groups;

class Report
{
    /**
     * @Groups({"result_dto", "student_iac"})
     * @var DateRange
     */
    private $range;

    /**
     ** @Groups({"result_dto"})
     * @var ReportHeader
     */
    private $header;

    /**
     * @Groups({"result_dto", "student_iac"})
     * @var ArrayCollection
     */
    private $students;
    /**
     * @var bool
     */
    private $frontpage;

    /**
     * @var string[]
     */
    private $groups;

    /**
     * @var bool
     */
    private $commentPage;

    public function __construct(DateRange $dateRange, $frontpage = false, $commentPage = false)
    {
        $this->range = $dateRange;
        $this->students = new ArrayCollection;
        $this->groups = [];
        $this->frontpage = $frontpage;
        $this->commentPage = $commentPage;
    }

    public function addReportHeader()
    {
        $this->header = new ReportHeader();
        return $this->header;
    }

    public function intoStudent($data)
    {
        $id = NtUid::import($data['sId']);
        $stud = $this->hasStudent($id);
        if (!$stud) {
            if(!isset($data['sFirstName']) && !isset($data['sLastName'])) {
                throw new Exception('404');
            }
            $fn = $data['sFirstName'];
            $ln = $data['sLastName'];

            $group = isset($data['gName']) ? $data['gName'] : null;

            if ($group) {
                if (!in_array($group, $this->groups)) {
                    $this->groups[] = $group;
                }
            }
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

    /**
     * @return boolean
     */
    public function hasFrontpage()
    {
        return $this->frontpage;
    }

    /**
     * @return boolean
     */
    public function hasCommentPage() {
        return $this->commentPage;
    }

    /**
     * @return \string[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    public function sort()
    {
        /** @var StudentResult $student */
        foreach ($this->students as $student) {
            $student->sort();
        }
    }

    /**
     * @return ReportHeader
     */
    public function getHeader()
    {
        return $this->header;
    }


}