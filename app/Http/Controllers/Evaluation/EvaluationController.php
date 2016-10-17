<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 8/08/16
 * Time: 14:41
 */

namespace App\Http\Controllers\Evaluation;


use Anouar\Fpdf\Fpdf;
use App\Domain\DTO\StudentDTO;
use App\Domain\DTO\StudentResultsDTO;
use App\Domain\Model\Evaluation\EvaluationRepository;
use App\Domain\Model\Events\EventTrackingRepository;
use App\Domain\Model\Identity\GroupRepository;
use App\Domain\Model\Time\DateRange;
use App\Domain\Services\Evaluation\EvaluationService;
use App\Domain\NtUid;
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


    public function __construct(EvaluationService $evaluationService,
                                GroupRepository $groupRepository,
                                EvaluationRepository $evaluationRepository,
                                SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->evaluationService = $evaluationService;
        $this->groupRepo = $groupRepository;
        $this->evaluationRepo = $evaluationRepository;
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

    public function getSummary()
    {
        //TODO: PDF Report Service
        //TODO: generate frontpage / leervorderingen / ...
        /* $

         $pdf = App::make('dompdf.wrapper');
         $pdf->loadView('report', ['students' => $result]);
         return $pdf->stream();*/
        $result = $this->evaluationRepo->getSummary();

        $fpdf = new Fpdf();

        $fpdf->SetAutoPageBreak(false, 7);
        /** @var StudentDTO $item */
        foreach ($result as $item) {
            $fpdf->AddPage();

            $fpdf->AddFont('Roboto', '', 'Roboto-Regular.php');
            $fpdf->AddFont('Roboto', 'bold', 'Roboto-Bold.php');
            $fpdf->SetFont('Roboto', 'bold', 18);

            $blue = [41, 51, 119];
            $orange = [255, 144, 0];

            $vbsde = 'VBS De';
            $wVBS = $fpdf->GetStringWidth($vbsde) + 2;

            $klimtoren = 'Klimtoren';
            $wKl = $fpdf->GetStringWidth($klimtoren);

            call_user_func_array([$fpdf, 'SetTextColor'], $blue);
            $fpdf->Cell($wVBS, 5, $vbsde);

            call_user_func_array([$fpdf, 'SetTextColor'], $orange);
            $fpdf->Cell($wKl, 5, $klimtoren, 0, 1);

            $fpdf->SetY(-42);
            call_user_func_array([$fpdf, 'SetTextColor'], $blue);
            $fpdf->SetFontSize(35);
            $fpdf->Cell(0, 10, 'EVALUATIES', 0, 1);

            call_user_func_array([$fpdf, 'SetTextColor'], $orange);
            $fpdf->SetFontSize(60);
            $fpdf->Cell(0, 25, $item->getDisplayName(), 0, 1);


            $fpdf->AddPage();
            $fpdf->AcceptPageBreak();
            $fpdf->SetFont('Roboto', '', 12);
            call_user_func_array([$fpdf, 'SetTextColor'], $blue);

            /** @var StudentResultsDTO $result */
            foreach ($item->getResults() as $result) {
                $fpdf->Cell(0, 12, $result->branch . ': ' . round($result->result,1) . '/' . $result->max, 0, 1);
            }
        }

        $fpdf->Output();
        exit;

    }
}