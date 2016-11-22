<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 7/11/16
 * Time: 20:34
 */

namespace App\Domain\Services\Evaluation;


use App\Domain\Model\Education\Goal;
use App\Domain\Model\Evaluation\IAC;
use App\Domain\Model\Evaluation\IACGoal;
use App\Domain\Model\Evaluation\IACRepository;
use App\Domain\Model\Identity\GroupRepository;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Identity\StudentRepository;
use App\Domain\Model\Reporting\Report;
use App\Domain\Model\Reporting\StudentResult;
use App\Domain\Model\Time\DateRange;
use App\Domain\NtUid;
use App\Domain\Services\Education\BranchService;
use App\Domain\Services\Identity\StudentService;
use App\Domain\Services\TrackService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

class IacService
{
    /** @var IACRepository */
    private $iacRepo;
    /** @var StudentService */
    private $studentService;
    /**
     * @var BranchService
     */
    private $branchService;
    /**
     * @var TrackService
     */
    private $trackService;
    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * IacService constructor.
     * @param IACRepository $iacRepository
     * @param StudentService $studentService
     * @param BranchService $branchService
     * @param TrackService $trackService
     */
    public function __construct(IACRepository $iacRepository,
                                StudentService $studentService, BranchService $branchService,
                                GroupRepository $groupRepository,
                                TrackService $trackService)
    {
        $this->iacRepo = $iacRepository;
        $this->studentService = $studentService;
        $this->branchService = $branchService;
        $this->trackService = $trackService;
        $this->groupRepository = $groupRepository;
    }

    /**
     * @param $id
     * @return IAC
     */
    public function get($id)
    {
        $id = NtUid::import($id);
        return $this->iacRepo->get($id);
    }

    /**
     * @param $id
     * @return Goal
     */
    public function getGoal($id)
    {
        $id = NtUid::import($id);
        return $this->iacRepo->getGoal($id);
    }

    /* ***************************************************
     * IAC
     * **************************************************/
    public function addIac($studentId, $data)
    {
        $start = $data['start'];
        if ($start) {
            $start = convert_date_from_string($start);
        }
        $end = $data['end'];
        if ($end) {
            $end = convert_date_from_string($end);
        }
        $daterange = DateRange::fromData(['start' => $start, 'end' => $end]);
        $branchId = $data['branch']['id'];
        $goals = $data['iacGoals'];


        /** @var Student $student */
        $student = $this->studentService->get(NtUid::import($studentId));
        $branch = null;
        if ($branchId != null) {
            $branch = $this->branchService->getBranch(NtUid::import($branchId));
        }

        if ($branch) {
            $iac = new IAC($student, $branch, $daterange);
            foreach ($goals as $goal) {
                $id = isset($goal['id']) ? $goal['id'] : $goal['goal']['id'];
                $goal = $this->getGoal($id);
                $iac->addGoal($goal);
            }
            $this->iacRepo->insert($iac);
            $this->trackService->track($data['auth_token'], 'iacs', 'insert', $iac->getId());
            return $iac;
        }

        return null;


    }


    /**
     * @param $iacId
     * @param $data
     * @return null
     */
    public function updateIac($iacId, $data)
    {
        $iac = $this->get(NtUid::import($iacId));

        $start = $data['start'];
        if ($start) {
            $start = convert_date_from_string($start);
        }
        $end = $data['end'];
        if ($end) {
            $end = convert_date_from_string($end);
        }
        $daterange = DateRange::fromData(['start' => $start, 'end' => $end]);
        $iac->setDateRange($daterange);

        $dbIds = [];
        /** @var IACGoal $item */
        foreach ($iac->allIACGoals()->toArray() as $item) {
            $dbIds[] = (string)$item->getId();
        }

        $dataGoals = $data['iacGoals'];
        $dataIds = [];
        foreach ($dataGoals as $dataGoal) {
            if (isset($dataGoal['id']) && isset($dataGoal['goal'])) {
                $ntId = NtUid::import($dataGoal['id']);
                $ig = null;
                /** @var IACGoal $ig */
                foreach ($iac->allIACGoals() as $iacGoal) {
                    if ($iacGoal->getId() == $ntId) {
                        $ig = $iacGoal;
                        break;
                    }
                }
                if ($ig) {
                    if (array_key_exists('achieved', $dataGoal)) {
                        $achieved = $dataGoal['achieved'];
                        if ($achieved == null) {
                            $ig->clearAchieved();
                        } else {
                            $ig->setAchieved($achieved);
                        }
                    }
                    if (array_key_exists('practice', $dataGoal)) {
                        $practice = $dataGoal['practice'];
                        if ($practice == null) {
                            $ig->clearPractice();
                        } else {
                            $ig->setPractice($practice);
                        }
                    }
                    if (array_key_exists('comment', $dataGoal)) {
                        $comment = $dataGoal['comment'];
                        $ig->setComment($comment);
                    }

                    //keep track of data-ids, so we can calculate the difference for deleted ones
                    $dataIds[] = $dataGoal['id'];
                }
            } else if (isset($dataGoal['id']) && !isset($dataGoal['goal'])) {
                //Adding new ones
                $gId = $dataGoal['id'];
                $goal = $this->getGoal($gId);
                $iac->addGoal($goal);
            }
        }

        $deleted = array_diff($dbIds, $dataIds);

        foreach ($deleted as $del) {
            $criteria = Criteria::create()
                ->where(Criteria::expr()->eq("id", $del));
            $ig = $iac->allIACGoals()->matching($criteria)->first();
            $iac->removeIACGoal($ig);
        }

        $this->iacRepo->update($iac);
        return $iac;
    }

    public function deleteIAC($iacId)
    {
        $iac = $this->get($iacId);
        $this->iacRepo->remove($iac);
    }

    public function getIACsForStudent($studentId, DateRange $range)
    {
        //TODO:

        //when route is /student/{id}/iac
        /*
         * Should'nt the response be:
         * IAC: [id, start, end, majors[id,name, branches[id,name,goals[id, text, achieved, practive,date, commetn]]]]
         * ???
         * How can this be done
         *
         */
        $id = NtUid::import($studentId);
        $student = $this->studentService->get($id);
        $data = $this->iacRepo->iacForStudent($studentId,  DateRange::infinite());
        //return $data;
        return $this->generatePerStudent($data, $student);
    }

    public function getIacsForGroup($groupId)
    {
        $id = NtUid::import($groupId);
        $group = $this->groupRepository->get($id);
        $data = $this->iacRepo->getIacForGroup($group, DateRange::infinite());
        return $data;

    }

    private function generateIac($data, DateRange $range)
    {
        $iac = new Report($range);
        foreach ($data as $item) {
            $iac->intoStudent($item)
                ->intoMajor($item)
                ->intoBranch($item)
                ->intoIac($item)
                ->intoGoal($item);
        }

        return $iac;
    }


    private function generatePerStudent($data, Student $student)
    {
        $student = new StudentResult($student->getId(), $student->getFirstName(), $student->getLastName(), null, null, null);
        foreach ($data as $item) {
            $student->intoMajor($item)
                ->intoBranch($item)
                ->intoIac($item)
                ->intoGoal($item);
        }
        return $student->getMajorResults();
    }


}