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

    public function __construct(EvaluationRepository $evaluationRepository, IACRepository $iacRepository)
    {
        $this->evaluationRepo = $evaluationRepository;
        $this->iacRepo = $iacRepository;
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
    public function getReport($group, DateRange $range)
    {
        $data = $this->evaluationRepo->getReportsForGroup($group, $range);
        $report = new Report($range);
        return $this->generateResultsReport($report, $data);
    }

    public function getReportByStudents($students, DateRange $range)
    {
        $data = $this->evaluationRepo->getReportsForStudents($students, $range);
        $report = new Report($range);
        return $this->generateResultsReport($report, $data);
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
}