<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 16/11/16
 * Time: 14:45
 */

namespace App\Http\Controllers\Evaluation;


use App\Domain\Model\Reporting\Report;
use App\Domain\Model\Time\DateRange;
use App\Domain\Services\Pdf\Report2PdfService;
use App\Domain\Services\Reporting\ReportingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JMS\Serializer\SerializerInterface;

class ReportController extends Controller
{
    /**
     * @var ReportingService
     */
    private $reportingService;
    /**
     * @var Report2PdfService
     */
    private $pdfService;

    public function __construct(ReportingService $reportingService, Report2PdfService $pdfService,
                                SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->reportingService = $reportingService;
        $this->pdfService = $pdfService;
    }

    public function pdfByGroup(Request $request, $groupId)
    {
        $report = $this->byGroup($request, $groupId);
        $this->generatePdf($report);
    }


    public function pdfByStudent(Request $request, $studentId)
    {
        $report = $this->byStudent($request, $studentId);
        $this->generatePdf($report);
    }

    public function pdfCustom(Request $request)
    {

    }

    public function jsonByGroup(Request $request, $groupId)
    {
        $report = $this->byGroup($request, $groupId);
        $this->generateJson($report);
    }

    public function jsonByStudent(Request $request, $studentId)
    {
        $report = $this->byStudent($request, $studentId);
        return  $this->generateJson($report);
    }

    public function jsonCustom(Request $request)
    {

    }


    private function generatePdf(Report $report)
    {
        $pdf = $this->pdfService
            ->report($report);

        //TODO: if with front page requested
        $pdf->withFrontPage();

        $pdf->build();
    }

    private function generateJson(Report $report)
    {
        return $this->response($report, ['result_dto', 'student_iac']);
    }

    private function byGroup(Request $request, $groupId)
    {
        $range = $this->getRange($request);

        $report = $this->reportingService->getReportByGroup($groupId, $range);
        return $report;
    }
    private function byStudent(Request $request, $studentId) {
        $range = $this->getRange($request);
        $report = $this->reportingService->getReportByStudent($studentId, $range);
        return $report;
    }

    /**
     * @param Request $request
     * @return DateRange
     */
    private function getRange(Request $request)
    {
        if ($request->has('start')) {
            $start = $request->get('start');
            $end = $request->get('end');
        } else {
            $start = '2016-09-01';
            $end = '2016-12-31';
        }
        $range = DateRange::fromData(['start' => $start, 'end' => $end]);
        return $range;
    }


}