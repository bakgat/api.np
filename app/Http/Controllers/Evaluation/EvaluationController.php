<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 8/08/16
 * Time: 14:41
 */

namespace App\Http\Controllers\Evaluation;



use Anouar\Fpdf\Fpdf;
use App\Domain\DTO\Results\BranchResultsDTO;
use App\Domain\DTO\Results\MajorResultsDTO;
use App\Domain\DTO\Results\PointResultDTO;
use App\Domain\DTO\Results\ReportDTO;
use App\Domain\DTO\Results\StudentResultsDTO;
use App\Domain\Model\Evaluation\EvaluationRepository;
use App\Domain\Model\Events\EventTrackingRepository;
use App\Domain\Model\Identity\GroupRepository;
use App\Domain\Model\Time\DateRange;
use App\Domain\Services\Evaluation\EvaluationService;
use App\Domain\NtUid;
use App\Domain\Services\Pdf\Ntpdf;
use App\Domain\Services\Pdf\Report2PdfService;
use App\Domain\Services\Reporting\ReportingService;
use App\Http\Controllers\Controller;
use DateTime;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use JMS\Serializer\SerializerInterface;

class EvaluationController extends Controller
{
    /** @var GroupRepository */
    private $groupRepo;
    /** @var EvaluationRepository */
    private $evaluationRepo;
    /** @var EvaluationService */
    private $evaluationService;
    /** @var Report2PdfService */
    private $pdfService;
    /** @var  ReportingService */
    private $reportingService;


    public function __construct(EvaluationService $evaluationService,
                                GroupRepository $groupRepository,
                                EvaluationRepository $evaluationRepository,
                                Report2PdfService $pdfService,
                                ReportingService $reportingService,
                                SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->evaluationService = $evaluationService;
        $this->groupRepo = $groupRepository;
        $this->pdfService = $pdfService;
        $this->evaluationRepo = $evaluationRepository;
        $this->reportingService = $reportingService;
    }

    public function index(Request $request)
    {
        //TODO: check for existence
        $groupId = $request->get('group');
        if ($request->has('start')) {
            $start = DateTime::createFromFormat('Y-m-d', $request->get('start'));
        } else {
            $start = DateTime::createFromFormat('Y-m-d', DateRange::PAST);
        }
        if ($request->has('end')) {
            $end = DateTime::createFromFormat('Y-m-d', $request->get('end'));
        } else {
            $end = DateTime::createFromFormat('Y-m-d', DateRange::PAST);
        }

        $group = $this->groupRepo->get(NtUid::import($groupId));
        $evaluations = $this->evaluationRepo->allEvaluationsForGroup($group, $start, $end);
        return $this->response($evaluations, ['group_evaluations']);
    }

    public function show($id)
    {
        if (!$id instanceof NtUid) {
            $id = NtUid::import($id);
        }
        $evaluation = $this->evaluationRepo->get($id);
        $evType = $evaluation->getEvaluationType()->getValue();
        $typeGroup = strtolower($evType) . '_evaluation_detail';
        return $this->response($evaluation, ['evaluation_detail', $typeGroup], true);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'max' => 'numeric',
            'branchForGroup.id' => 'required',
            'date' => 'required'
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 422);
        }

        $data = $request->all();
        $data['auth_token'] = $request->header('Auth');
        $evaluation = $this->evaluationService->create($data);
        return $this->response($evaluation, ['evaluation_detail']);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'max' => 'numeric',
            'branchForGroup.id' => 'required',
            'date' => 'required'
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 422);
        }

        $data = $request->all();
        $data['auth_token'] = $request->header('Auth');
        $evaluation = $this->evaluationService->update($data);
        return $this->response($evaluation, ['evaluation_detail']);
    }

    /**
     *
     */
    public function getSummary()
    {
        $report = $this->reportingService->getReport();

        $this->pdfService
            ->report($report)
            ->withFrontPage()
            ->build();

        //return $this->response($this->evaluationRepo->getSummary(), ['result_dto']);
        /*
        $range1 = ['start' => '2016-10-01', 'end' => '2016-12-31'];
        $range2 = ['start' => '2016-04-01', 'end' => '2016-06-30'];
        $report = new ReportDTO($range1);

        $st1 = new StudentResultsDTO('Karl', 'Van Iseghem', 'L3A', 'juf Ursula Baelde');
        $st2 = new StudentResultsDTO('Rebekka', 'Buyse', 'L3A', 'juf Ursula Baelde');

        $m1 = new MajorResultsDTO('wiskunde');
        $b1 = new BranchResultsDTO('getallenkennis');
        $rp1 = new PointResultDTO($range1, 13, 20, false);
        $re1 = new PointResultDTO($range1, 15, 20, true);

        $rp2 = new PointResultDTO($range2, 10, 20, false);
        $re2 = new PointResultDTO($range2, 18, 20, true);
        $b1->addPointResult($rp1)->addPointResult($re1)
            ->addPointResult($rp2)->addPointResult($re2);

        $b2 = new BranchResultsDTO('hoofdrekenen');
        $rp3 = new PointResultDTO($range1, 10, 20, false);
        $re3 = new PointResultDTO($range1, 13, 20, true);

        $rp4 = new PointResultDTO($range2, 15, 20, false);
        $re4 = new PointResultDTO($range2, 20, 20, true);
        $b2->addPointResult($rp3)->addPointResult($re3)
            ->addPointResult($rp4)->addPointResult($re4);


        $m1->addBranchResult($b1)->addBranchResult($b2);
        $m2 = new MajorResultsDTO('taal');
        $st1->addMajorResult($m1)->addMajorResult($m2);

        $m3 = new MajorResultsDTO('wiskunde');
        $m4 = new MajorResultsDTO('taal');
        $st2->addMajorResult($m3)->addMajorResult($m4);

        $report->addStudentResults($st1)
            ->addStudentResults($st2);

        $this->pdfService
            ->report($report)
            ->withFrontPage()
            ->build();
        */
    }
}