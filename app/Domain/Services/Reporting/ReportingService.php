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

class ReportingService
{
    /** @var EvaluationRepository  */
    private $evaluationRepo;

    public function __construct(EvaluationRepository $evaluationRepository)
    {
        $this->evaluationRepo = $evaluationRepository;
    }

    public function getReport()
    {
        $data = $this->evaluationRepo->getSummary();
        $report = new Report();
        foreach ($data as $item) {
            $report->intoStudent($item)
                ->intoMajor($item);
        }
        return $report;
    }
}