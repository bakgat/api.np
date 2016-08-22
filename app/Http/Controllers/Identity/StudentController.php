<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 20/06/16
 * Time: 11:29
 */

namespace App\Http\Controllers\Identity;


use App\Domain\Model\Identity\GroupRepository;
use App\Domain\Model\Identity\StudentRepository;
use App\Domain\Services\Identity\StudentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JMS\Serializer\SerializerInterface;
use Webpatser\Uuid\Uuid;

class StudentController extends Controller
{
    /** @var StudentRepository studentRepo */
    private $studentRepo;
    /** @var GroupRepository */
    private $groupRepo;
    /** @var StudentService */
    private $studentService;

    public function __construct(StudentRepository $studentRepo,
                                GroupRepository $groupRepository,
                                StudentService $studentService,
                                SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->studentRepo = $studentRepo;
        $this->groupRepo = $groupRepository;
        $this->studentService = $studentService;
    }

    public function index(Request $request)
    {
        if ($request->has('flat')) {
            $field = $request->get('flat');
            return $this->response($this->studentRepo->flat($field));
        }
        if ($request->has('group')) {
            $groupId = Uuid::import($request->get('group'));
            $group = $this->groupRepo->get($groupId);
            return $this->response($this->studentRepo->allActiveInGroup($group), ['student_list']);
        }

        return $this->response($this->studentRepo->all(), ['student_list']);
    }

    public function show($id)
    {
        if (!$id instanceof Uuid) {
            $id = Uuid::import($id);
        }
        return $this->response($this->studentRepo->find($id), ['student_detail']);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'schoolId' => 'required|unique:students,school_id',
            'gender' => 'required|in:M,F,O',
            'group.id' => 'required',
            'groupnumber' => 'required',
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 422);
        }

        $student = $this->studentService->create($request->all());
        return $this->response($student, ['student_detail']);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'schoolId' => 'required|unique:students,school_id,' . $request->get('id'),
            'gender' => 'required|in:M,F,O',
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 422);
        }

        $student = $this->studentService->update($request->all());
        return $this->response($student, ['student_detail']);
    }

    /* ***************************************************
     * GROUPS
     * **************************************************/
    public function allGroups($id)
    {
        if (!$id instanceof Uuid) {
            $id = Uuid::import($id);
        }
        //TODO GET FROM STUDENT OBJECT
        return $this->response($this->studentRepo->allGroups($id), ['student_groups']);
    }

    public function joinGroup(Request $request, $id)
    {
        $start = $request->get('start');
        if ($start) {
            $start = convert_date_from_string($start);
        }
        $end = $request->get('end');
        if ($end) {
            $end = convert_date_from_string($end);
        }
        $group = $request->get('group');
        $number = $request->get('number');

        $studentGroup = $this->studentService->joinGroup($id, $group['id'], $number, $start, $end);
        return $this->response($studentGroup, ['student_groups']);
    }

    public function updateGroup(Request $request, $studentGroupId)
    {
        $start = $request->get('start');
        if ($start) {
            $start = convert_date_from_string($start);
        }
        $end = $request->get('end');
        if ($end) {
            $end = convert_date_from_string($end);
        }
        $number = $request->get('number');

        $studentGroup = $this->studentService->updateGroup($studentGroupId, $number, $start, $end);
        return $this->response($studentGroup, ['student_groups']);
    }

    /* ***************************************************
     * REDICODI
     * **************************************************/
    public function allRedicodi($id)
    {
        if (!$id instanceof Uuid) {
            $id = Uuid::import($id);
        }
        return $this->response($this->studentRepo->allRedicodi($id), ['student_redicodi']);
    }

    public function addRedicodi(Request $request, $id)
    {
        //TODO: validation !
        //TODO: move to service with all this data !!!
        //TODO: same for other add / update functions
        $start = $request->get('start');
        if ($start) {
            $start = convert_date_from_string($start);
        }
        $end = $request->get('end');
        if ($end) {
            $end = convert_date_from_string($end);
        }
        $redicodi = $request->get('redicodi')['id'];
        $branchId = $request->get('branch')['id'];
        $content = $request->get('content');

        $studentRedicodi = $this->studentService->addRedicodi($id, $branchId, $redicodi, $content, $start, $end);
        return $this->response($studentRedicodi, ['student_redicodi']);
    }

    public function updateRedicodi(Request $request, $studentRedicodiId)
    {
        $start = $request->get('start');
        if ($start) {
            $start = convert_date_from_string($start);
        }
        $end = $request->get('end');
        if ($end) {
            $end = convert_date_from_string($end);
        }
        $redicodi = $request->get('redicodi')['id'];
        $branchId = $request->get('branch')['id'];
        $content = $request->get('content');

        $studentRedicodi = $this->studentService->updateRedicodi($studentRedicodiId, $branchId, $redicodi, $content, $start, $end);
        return $this->response($studentRedicodi, ['student_redicodi']);
    }
}