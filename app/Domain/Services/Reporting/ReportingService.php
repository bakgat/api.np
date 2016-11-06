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
use App\Domain\NtUid;

class ReportingService
{
    /** @var EvaluationRepository */
    private $evaluationRepo;

    public function __construct(EvaluationRepository $evaluationRepository)
    {
        $this->evaluationRepo = $evaluationRepository;
    }

    public function getFullReport()
    {
        $data = $this->evaluationRepo->getSummary();

        return $this->generateReport($data);
    }
    // TODO: per group
    // TODO: per student
    // TODO: per range / oldrange
    // TODO: any combination per branch, per major, per oldrange
    // TODO: making graphs for any combination above (history per group, per student, per branch, per major, per range, per oldrange)
    // TODO: what about graphs in 1st and 2nd grade?
    // TODO: 
    public function getReport($group)
    {
        $data = $this->evaluationRepo->getReportsForGroup($group, null);
        return $this->generateReport($data);
    }

    public function getReportByStudents($students)
    {
        $data = $this->evaluationRepo->getReportsForStudents($students, null);
        return $this->generateReport($data);
    }

    /**
     * @param $data
     * @return Report
     */
    private function generateReport($data)
    {
        $report = new Report();
        foreach ($data as $item) {
            $report->intoStudent($item)
                ->intoMajor($item)
                ->intoBranch($item)
                ->intoHistory($item);
        }

        return $report;
    }
}