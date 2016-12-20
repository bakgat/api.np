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
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Identity\StudentRepository;
use App\Domain\Model\Reporting\Report;
use App\Domain\Model\Time\DateRange;
use App\Domain\NtUid;

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

    public function __construct(StudentRepository $studentRepository, EvaluationRepository $evaluationRepository, IACRepository $iacRepository)
    {
        $this->evaluationRepo = $evaluationRepository;
        $this->iacRepo = $iacRepository;
        $this->studentRepository = $studentRepository;
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
    public function getReportByGroup($group, DateRange $range, $render = 'wf')
    {
        $withFrontPage = $render == 'all';
        $onlyFrontPage = $render == 'f';

        //@todo: handle only front page
        //@todo: now all student info relies on point_results data
        //      student info (current group, teacher, names, parents, ...) must be seperate query, I guess

        $pointResults = $this->evaluationRepo->getPointReportForGroup($group, $range);
        $comprehensiveResults = $this->evaluationRepo->getComprehensiveReportForGroup($group, $range);
        $spokenResults = $this->evaluationRepo->getSpokenReportForGroup($group, $range);
        $mcResults = $this->evaluationRepo->getMultiplechoiceReportForGroup($group, $range);
        $iacs = $this->iacRepo->getFlatIacForGroup($group, $range);
        $feedback = $this->evaluationRepo->getFeedbackReportForGroup($group, $range);
        $redicodi = $this->evaluationRepo->getRedicodiReportForGroup($group, $range);


        $report = new Report($range, $withFrontPage);
        $this->generateResultsReport($report, $pointResults);
        $this->generateComprehensiveReport($report, $comprehensiveResults);
        $this->generateSpokenReport($report, $spokenResults);
        $this->generateMcResultsReport($report, $mcResults);
        $this->generateIacsReport($report, $iacs);
        $this->generateFeedbackReport($report, $feedback);
        $this->generateRedicodiReport($report, $redicodi);

        $report->sort();

        return $report;
    }

    public function getReportByStudents($studentIds, $range)
    {
        
        $pointResults = $this->evaluationRepo->getPointReportForStudents($studentIds, $range);
        $comprehensiveResults = $this->evaluationRepo->getComprehensiveReportForStudents($studentIds, $range);
        $spokenResults = $this->evaluationRepo->getSpokenReportForStudents($studentIds, $range);
        $mcResults = $this->evaluationRepo->getMultiplechoiceReportForStudents($studentIds, $range);
        $iacs = $this->iacRepo->getFlatIacForStudents($studentIds, $range);
        $feedback = $this->evaluationRepo->getFeedbackReportForStudents($studentIds, $range);
        $redicodi = $this->evaluationRepo->getRedicodiReportForStudents($studentIds, $range);


        $report = new Report($range);
        $this->generateResultsReport($report, $pointResults);
        $this->generateComprehensiveReport($report, $comprehensiveResults);
        $this->generateSpokenReport($report, $spokenResults);
        $this->generateMcResultsReport($report, $mcResults);
        $this->generateIacsReport($report, $iacs);
        $this->generateFeedbackReport($report, $feedback);
        $this->generateRedicodiReport($report, $redicodi);

        return $report;
    }



    /**
     * @param Report $report
     * @param $data
     * @return Report
     */
    private function generateResultsReport(Report $report, $data)
    {

        foreach ($data as $item) {
            $report->intoStudent($item)
                ->intoMajor($item)
                ->intoBranch($item)
                ->intoHistory($item);
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

}