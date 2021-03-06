<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 16/11/16
 * Time: 14:45
 */

namespace App\Http\Controllers\Evaluation;


use App\Domain\Model\Reporting\Report;
use App\Domain\Model\Reporting\StudentResult;
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
        $this->generatePdf($report, true);
    }


    public function pdfByStudents(Request $request)
    {
        $report = $this->byStudents($request);
        $this->generatePdf($report, false);
    }

    public function pdfCustom(Request $request)
    {

    }

    public function pivotByGroup(Request $request, $groupId)
    {
        $report = $this->byGroup($request, $groupId);
        $this->generatePivot($report);
    }

    public function jsonByGroup(Request $request, $groupId)
    {
        $report = $this->byGroup($request, $groupId);
        return $this->generateJson($report);
    }

    public function jsonByStudents(Request $request)
    {
        $report = $this->byStudents($request);
        return $this->generateJson($report);
    }

    public function jsonCustom(Request $request)
    {

    }


    private function generatePdf(Report $report, $byGroup = true)
    {
        $pdf = $this->pdfService
            ->report($report);

        if ($byGroup) {
            $name = array_first($report->getGroups());
            if (count($report->getGroups()) > 1) {
                $name .= '-' . array_last($report->getGroups());
            }
        } else {
            /** @var StudentResult $firstStud */
            $firstStud = $report->getStudentResults()->first();
            $name = $firstStud->getLastName();
            if (count($report->getStudentResults()) > 1) {
                /** @var StudentResult $lastStud */
                $lastStud = $report->getStudentResults()->last();
                $name .= '-' . $lastStud->getLastName();
            }
        }
        $pdf->build($name);
    }
    private function generatePivot(Report $report)
    {
        $pdf = $this->pdfService
            ->report($report);

        $name = array_first($report->getGroups());
        if (count($report->getGroups()) > 1) {
            $name .= '-' . array_last($report->getGroups());
        }

        $pdf->buildPivot($name);
    }


    private function generateJson(Report $report)
    {
        return $this->response($report, ['result_dto', 'student_iac']);
    }

    private function byGroup(Request $request, $groupId)
    {
        $range = $this->getRange($request);
        $render = $request->has('render') ? $request->get('render') : 'nf';
        $token = $request->hasHeader('Auth') ? $request->header('Auth') : $request->get('token');
        $report = $this->reportingService->getReportByGroup($groupId, $range, $render, $token);
        return $report;
    }

    private function byStudents(Request $request)
    {
        $id = $request->get('id');
        $ids = explode(',', $id);
        $range = $this->getRange($request);
        $render = $request->has('render') ? $request->get('render') : 'nf';
        $token = $request->hasHeader('Auth') ? $request->header('Auth') : $request->get('token');
        $report = $this->reportingService->getReportByStudents($ids, $range, $render, $token);
        return $report;
    }

    /**
     * @param Request $request
     * @return DateRange
     */
    private function getRange(Request $request)
    {
        if ($request->has('qstart')) {
            $start = $request->get('qstart');
            $end = $request->get('qend');
        } else {
            $start = '2016-09-01';
            $end = '2016-12-31';
        }
        $range = DateRange::fromData(['start' => $start, 'end' => $end]);
        return $range;
    }



}