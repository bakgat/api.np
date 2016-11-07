<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 3/11/16
 * Time: 09:58
 */

namespace App\Domain\Services\Reporting;


use App\Domain\Model\Evaluation\EvaluationRepository;
use App\Domain\Model\Reporting\Report;
use App\Domain\Model\Time\DateRange;
use App\Domain\NtUid;

class ReportingService
{
    /** @var EvaluationRepository */
    private $evaluationRepo;

    public function __construct(EvaluationRepository $evaluationRepository)
    {
        $this->evaluationRepo = $evaluationRepository;
    }

    public function getFullReport(DateRange $range)
    {
        $data = $this->evaluationRepo->getSummary($range);

        return $this->generateReport($data, $range);
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
        return $this->generateReport($data, $range);
    }

    public function getReportByStudents($students, DateRange $range)
    {
        $data = $this->evaluationRepo->getReportsForStudents($students, $range);
        return $this->generateReport($data, $range);
    }

    /**
     * @param $data
     * @return Report
     */
    private function generateReport($data, DateRange $range)
    {
        $report = new Report($range);
        foreach ($data as $item) {
            $report->intoStudent($item)
                ->intoMajor($item)
                ->intoBranch($item)
                ->intoHistory($item);
        }

        return $report;
    }
}