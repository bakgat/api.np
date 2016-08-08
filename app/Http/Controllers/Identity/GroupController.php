<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 20/07/16
 * Time: 22:40
 */

namespace App\Http\Controllers\Identity;


use App\Domain\Model\Evaluation\EvaluationRepository;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\GroupRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Webpatser\Uuid\Uuid;

class GroupController extends Controller
{
    /** @var GroupRepository */
    private $groupRepo;

    public function __construct(GroupRepository $groupRepository, SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->groupRepo = $groupRepository;

    }


    public function index(Request $request)
    {
        if ($request->has('active') && $request->get('active') == 'true') {
            return $this->response($this->groupRepo->allActive(), ['group']);
        }
        return $this->response($this->groupRepo->all(), ['group']);
    }


    public function show($id)
    {
        if (!$id instanceof Uuid) {
            $id = Uuid::import($id);
        }
        return $this->response($this->groupRepo->find($id), ['group']);
    }

    public function allActiveStudents($id)
    {
        if (!$id instanceof Uuid) {
            $id = Uuid::import($id);
        }
        $activeStudents = $this->groupRepo->allActiveStudents($id);

        return $this->response($activeStudents, ['group_students']);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'unique:groups,name,' . $request->get('id') . ',id'
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 422);
        }

        $id = Uuid::import($request->get('id'));
        $group = $this->groupRepo->get($id);

        $group->updateName($request->get('name'));
        if ($request->has('active')) {
            if ($request->get('active')) {
                $group->activate();
            } else {
                $group->block();
            }
        }

        $this->groupRepo->update($group);

        return $this->response($group, ['group']);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:groups'
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 422);
        }

        $name = $request->get('name');

        //Let Domain Model decide what default is for "active"
        if ($request->has('active')) {
            $active = $request->get('active');
            $group = new Group($name, $active);
        } else {
            $group = new Group($name);
        }


        $this->groupRepo->insert($group);


        return $this->response($group, ['group']);
    }


}