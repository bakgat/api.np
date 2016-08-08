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
use App\Domain\Uuid;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JMS\Serializer\SerializerInterface;

class EvaluationController extends Controller
{
    /** @var GroupRepository */
    private $groupRepo;
    /** @var EvaluationRepository */
    private $evaluationRepo;

    public function __construct(GroupRepository $groupRepository, EvaluationRepository $evaluationRepository, SerializerInterface $serializer)
    {
        parent::__construct($serializer);
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
        if(!$id instanceof Uuid) {
            $id = Uuid::import($id);
        }
        $evaluation = $this->evaluationRepo->get($id);
        return $this->response($evaluation, ['evaluation_detail']);
    }
}