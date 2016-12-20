<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 25/11/16
 * Time: 20:57
 */

namespace App\Domain\Services\Pdf;


use App\Domain\Model\Evaluation\EvaluationRepository;
use App\Domain\Model\Reporting\Report;

class Report2PdfService
{
    /**
     * @var EvaluationRepository
     */
    private $evaluationRepo;
    /**
     * @var Report
     */
    private $report;
    /**
     * @var PdfReport
     */
    private $pdfReport;

    /**
     * Report2PdfService constructor.
     * @param EvaluationRepository $evaluationRepository
     */
    public function __construct(EvaluationRepository $evaluationRepository)
    {
        $this->evaluationRepo = $evaluationRepository;
    }

    public function report(Report $report)
    {
        $this->report = $report;
        return $this;
    }

    public function build($name)
    {
        $this->pdfReport = new PdfReport($this->report);
        $this->pdfReport
            ->output($name);
    }

   
}