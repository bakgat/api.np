<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 6/08/16
 * Time: 23:00
 */

namespace App\Domain\Model\Evaluation;


use App\Domain\Model\Education\BranchForGroup;
use App\Domain\Model\Education\Major;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Time\DateRange;
use App\Domain\NtUid;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

interface EvaluationRepository
{
    public function allEvaluations();

    public function allEvaluationsForGroup(Group $group, DateTime $start, DateTime $end);

    /**
     * @param NtUid $id
     * @return Evaluation
     */
    public function get(NtUid $id);

    /**
     * @param $evaluation
     * @return int
     */
    public function update(Evaluation $evaluation);

    /**
     * @param $evaluation
     * @return NtUid
     */
    public function insert(Evaluation $evaluation);

    /**
     * @return ArrayCollection
     */
    public function getSummary(DateRange $range);

    /**
     * @param $studentId
     * @param $range
     * @return ArrayCollection
     */
    public function getPointReportForStudent($studentId, $range);

    public function getPointReportForStudents($studentIds, DateRange $range);

    /**
     * @param $group
     * @param DateRange $range
     * @return ArrayCollection
     */
    public function getPointReportForGroup($group, DateRange $range);

    /**
     * @param $group
     * @param DateRange $range
     * @return ArrayCollection
     */
    public function getHeadersReportForGroup($group, DateRange $range);

    /**
     * @param $group
     * @param DateRange $range
     * @return ArrayCollection
     */
    public function getComprehensiveReportForGroup($group, DateRange $range);

    /**
     * @param $group
     * @param $range
     * @return ArrayCollection
     */
    public function getFeedbackReportForGroup($group, DateRange $range);

    /**
     * @param $group
     * @param DateRange $range
     * @return ArrayCollection
     */
    public function getSpokenReportForGroup($group, DateRange $range);

    /**
     * @param $group
     * @param DateRange $range
     * @return ArrayCollection
     */
    public function getMultiplechoiceReportForGroup($group, DateRange $range);

    /**
     * @param $group
     * @param DateRange $range
     * @return ArrayCollection
     */
    public function getRedicodiReportForGroup($group, DateRange $range);


    public function getReportsForStudentsByMajor($studentIds, $range, Major $major);

    public function getReportsForGroupByMajor(Group $group, $range, Major $major);

    /**
     * @return int
     */
    public function count();

    /**
     * @param Evaluation $evaluation
     * @return boolean
     */
    public function remove(Evaluation $evaluation);

    /**
     * @param NtUid $id
     * @return EvaluationType
     */
    public function getType(NtUid $id);

    /**
     * @param NtUid $id
     * @return Evaluation
     */
    public function getFeedbackResults(NtUid $id);

    /**
     * @param NtUid $id
     * @return Evaluation
     */
    public function getMultiplechoiceResults(NtUid $id);

    public function getComprehensiveReportForStudents($studentIds, DateRange $range);

    public function getSpokenReportForStudents($studentIds, DateRange $range);

    public function getMultiplechoiceReportForStudents($studentId, DateRange $range);

    public function getFeedbackReportForStudents($studentIds, DateRange $range);

    public function getRedicodiReportForStudents($studentIds, DateRange $range);

    public function allRedicodiStats(DateTime $endDate);

    /**
     * @param GraphRange $graphRange
     * @param BranchForGroup $branchForGroup
     * @return mixed
     */
    public function allRangeResults(GraphRange $graphRange, BranchForGroup $branchForGroup);

    /**
     * @param GraphRange $graphRange
     * @param BranchForGroup $branchForGroup
     * @return mixed
     */
    public function allPointResults(GraphRange $graphRange, BranchForGroup $branchForGroup);

    public function updateOrCreateRR(RR $rr);

    public function getHistory($studentIds);

    public function getHistoryForGroup($group, DateRange $range);
}