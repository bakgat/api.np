<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 8/08/16
 * Time: 14:41
 */

namespace App\Http\Controllers\Evaluation;


use Anouar\Fpdf\Fpdf;
use App\Domain\Model\Evaluation\EvaluationRepository;
use App\Domain\Model\Evaluation\EvaluationType;
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
        if ($request->has('qstart')) {
            $start = DateTime::createFromFormat('Y-m-d', $request->get('qstart'));
        } else {
            $start = DateTime::createFromFormat('Y-m-d', DateRange::PAST);
        }
        if ($request->has('qend')) {
            $end = DateTime::createFromFormat('Y-m-d', $request->get('qend'));
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
        //TODO: check type of evaluation to return correct json
        $evType = $this->evaluationRepo->getType($id);
        if ($evType == EvaluationType::FEEDBACK) {
            $evaluation = $this->evaluationRepo->getFeedbackResults($id);
        } else if ($evType == EvaluationType::MULTIPLECHOICE) {
            $evaluation = $this->evaluationRepo->getMultiplechoiceResults($id);
        } else {
            $evaluation = $this->evaluationRepo->get($id);
        }
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

    public function destroy($id)
    {
        $result = $this->evaluationService->delete($id);
        return $this->response($result);
    }
}