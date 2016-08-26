<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 8/08/16
 * Time: 14:41
 */

namespace App\Http\Controllers\Evaluation;


use App\Domain\Model\Evaluation\EvaluationRepository;
use App\Domain\Model\Identity\GroupRepository;
use App\Domain\Services\Evaluation\EvaluationService;
use App\Domain\Uuid;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

    public function __construct(EvaluationService $evaluationService, GroupRepository $groupRepository, EvaluationRepository $evaluationRepository, SerializerInterface $serializer)
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

        $group = $this->groupRepo->get(Uuid::import($groupId));
        return $this->response($this->evaluationRepo->allEvaluationsForGroup($group), ['group_evaluations']);
    }

    public function show($id)
    {
        if (!$id instanceof Uuid) {
            $id = Uuid::import($id);
        }
        $evaluation = $this->evaluationRepo->get($id);
        return $this->response($evaluation, ['evaluation_detail'], true);
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
        $evaluation = $this->evaluationService->create($request->all());
        return $this->response($evaluation, ['evaluation_detail']);
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'max' => 'numeric',
            'branchForGroup.id' => 'required',
            'date' => 'required'
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 422);
        }
        $evaluation = $this->evaluationService->update($request->all());
        return $this->response($evaluation, ['evaluation_detail']);
    }
}