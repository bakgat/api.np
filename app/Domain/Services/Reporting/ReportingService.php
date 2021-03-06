<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 3/11/16
 * Time: 09:58
 */

namespace App\Domain\Services\Reporting;


use App\Domain\Model\Evaluation\EvaluationRepository;
use App\Domain\Model\Evaluation\IACRepository;
use App\Domain\Model\Events\EventTracking;
use App\Domain\Model\Events\EventTrackingRepository;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Identity\StudentRepository;
use App\Domain\Model\Reporting\Report;
use App\Domain\Model\Time\DateRange;
use App\Domain\NtUid;
use Exception;

class ReportingService
{
    /** @var EvaluationRepository */
    private $evaluationRepo;

    /**
     * @var IACRepository
     */
    private $iacRepo;
    /**
     * @var StudentRepository
     */
    private $studentRepository;
    /** @var EventTrackingRepository  */
    private $trackRepo;

    public function __construct(StudentRepository $studentRepository,
                                    EvaluationRepository $evaluationRepository,
                                    IACRepository $iacRepository,
                                    EventTrackingRepository $eventTrackingRepository)
    {
        $this->evaluationRepo = $evaluationRepository;
        $this->iacRepo = $iacRepository;
        $this->studentRepository = $studentRepository;
        $this->trackRepo = $eventTrackingRepository;
    }

    public function getFullReport(DateRange $range)
    {
        $data = $this->evaluationRepo->getSummary($range);
        $iacs = $this->iacRepo->getIacs();

        $report = new Report($range);
        $this->generateResultsReport($report, $data);
        $this->generateIacsReport($report, $iacs);

        return $report;
    }
    // TODO: per group
    // TODO: per student
    // TODO: per range / oldrange
    // TODO: any combination per branch, per major, per oldrange
    // TODO: making graphs for any combination above (history per group, per student, per branch, per major, per range, per oldrange)
    // TODO: what about graphs in 1st and 2nd grade?
    // TODO: 
    public function getReportByGroup($group, DateRange $range, $render = 'nf', $authToken)
    {
        $withFrontPage = $render == 'all';
        $withCommentPage = $render == 'nf' || $render == 'all';
        $onlyFrontPage = $render == 'f';


        //@todo: handle only front page
        //@todo: now all student info relies on point_results data
        //      student info (current group, teacher, names, parents, ...) must be seperate query, I guess

        $headers = $this->evaluationRepo->getHeadersReportForGroup($group, $range);
        $pointResults = $this->evaluationRepo->getPointReportForGroup($group, $range);
        $history = $this->evaluationRepo->getHistoryForGroup($group, $range);
        $comprehensiveResults = $this->evaluationRepo->getComprehensiveReportForGroup($group, $range);
        $spokenResults = $this->evaluationRepo->getSpokenReportForGroup($group, $range);
        $mcResults = $this->evaluationRepo->getMultiplechoiceReportForGroup($group, $range);
        $iacs = $this->iacRepo->getFlatIacForGroup($group, $range);
        $feedback = $this->evaluationRepo->getFeedbackReportForGroup($group, $range);
        $redicodi = $this->evaluationRepo->getRedicodiReportForGroup($group, $range);


        $report = new Report($range, $withFrontPage, $withCommentPage);
        $this->generateReportHeaders($report, $headers);
        $this->generateResultsReport($report, $pointResults, true);
        $this->generateResultsReport($report, $history);
        $this->generateComprehensiveReport($report, $comprehensiveResults);
        $this->generateSpokenReport($report, $spokenResults);
        $this->generateMcResultsReport($report, $mcResults);
        $this->generateIacsReport($report, $iacs);
        $this->generateFeedbackReport($report, $feedback);
        $this->generateRedicodiReport($report, $redicodi);

        $report->sort();

        $userId = $authToken;
        $track = new EventTracking('staff', $userId, 'report', 'report-group', $group);
        $track->setDetails($range->toString() . '|' . $render);
        $this->trackRepo->save($track);

        return $report;
    }

    public function getReportByStudents($studentIds, $range, $render, $authToken)
    {
        $withFrontPage = $render == 'all';
        $withCommentPage = $render == 'nf' || $render == 'all';
        $onlyFrontPage = $render == 'f';
        
        $pointResults = $this->evaluationRepo->getPointReportForStudents($studentIds, $range);
        $history = $this->evaluationRepo->getHistory($studentIds);
        $comprehensiveResults = $this->evaluationRepo->getComprehensiveReportForStudents($studentIds, $range);
        $spokenResults = $this->evaluationRepo->getSpokenReportForStudents($studentIds, $range);
        $mcResults = $this->evaluationRepo->getMultiplechoiceReportForStudents($studentIds, $range);
        $iacs = $this->iacRepo->getFlatIacForStudents($studentIds, $range);
        $feedback = $this->evaluationRepo->getFeedbackReportForStudents($studentIds, $range);
        $redicodi = $this->evaluationRepo->getRedicodiReportForStudents($studentIds, $range);


        $report = new Report($range, $withFrontPage, $withCommentPage);
        $this->generateResultsReport($report, $pointResults, true);
        $this->generateResultsReport($report, $history);
        $this->generateComprehensiveReport($report, $comprehensiveResults);
        $this->generateSpokenReport($report, $spokenResults);
        $this->generateMcResultsReport($report, $mcResults);
        $this->generateIacsReport($report, $iacs);
        $this->generateFeedbackReport($report, $feedback);
        $this->generateRedicodiReport($report, $redicodi);

        foreach ($studentIds as $item) {
            $userId = $authToken;
            $track = new EventTracking('staff', $userId, 'report', 'report-student', $item);
            $track->setDetails($range->toString() . '|' . $render);
            $this->trackRepo->save($track);
        }

        return $report;
    }



    /**
     * @param Report $report
     * @param $data
     * @return Report
     */
    private function generateResultsReport(Report $report, $data, $isCurrent = false)
    {

        foreach ($data as $item) {
            try {
                $report->intoStudent($item)
                    ->intoMajor($item)
                    ->intoBranch($item)
                    ->intoHistory($item, $isCurrent);
            } catch(Exception $ex) {
                if(!$ex->getMessage() == '404') {
                    //fall through if error was not 'student not found'
                    throw $ex;
                }
            }
        }

        return $report;
    }

    /**
     * @param Report $report
     * @param $data
     * @return Report
     */
    private function generateComprehensiveReport(Report $report, $data)
    {

        foreach ($data as $item) {
            $report->intoStudent($item)
                ->intoMajor($item)
                ->intoBranch($item)
                ->intoComprehensive($item);
        }

        return $report;
    }

    /**
     * @param Report $report
     * @param $data
     * @return Report
     */
    private function generateSpokenReport(Report $report, $data)
    {

        foreach ($data as $item) {
            $report->intoStudent($item)
                ->intoMajor($item)
                ->intoBranch($item)
                ->intoSpoken($item);
        }

        return $report;
    }

    /**
     * @param Report $report
     * @param $data
     * @return Report
     */
    private function generateMcResultsReport(Report $report, $data)
    {
        foreach ($data as $item) {
            $report->intoStudent($item)
                ->intoMajor($item)
                ->intoBranch($item)
                ->intoMultiplechoice($item);
        }

        return $report;
    }

    /**
     * @param Report $report
     * @param $iacs
     * @return Report
     */
    private function generateIacsReport(Report $report, $iacs)
    {
        foreach ($iacs as $item) {
            $report->intoStudent($item)
                ->intoMajor($item)
                ->intoBranch($item)
                ->intoIac($item)
                ->intoGoal($item);
        }

        return $report;
    }

    private function generateFeedbackReport(Report $report, $data)
    {
        foreach ($data as $item) {
            $report->intoStudent($item)
                ->intoFeedback($item);
        }
        return $report;
    }

    private function generateRedicodiReport(Report $report, $data)
    {
        foreach ($data as $item) {
            $report->intoStudent($item)
                ->intoRedicodi($item);
        }
    }

    private function generateReportHeaders(Report $report, $headers)
    {
        $header = $report->addReportHeader();
        foreach ($headers as $item) {
            $header
                ->intoMajor($item)
                ->intoBranch($item);
        }
    }

}