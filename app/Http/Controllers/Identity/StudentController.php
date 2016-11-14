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
use App\Domain\Model\Time\DateRange;
use App\Domain\NtUid;
use App\Domain\Services\Evaluation\IacService;
use App\Domain\Services\Identity\StudentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JMS\Serializer\SerializerInterface;

class StudentController extends Controller
{
    /** @var StudentRepository studentRepo */
    private $studentRepo;
    /** @var GroupRepository */
    private $groupRepo;
    /** @var StudentService */
    private $studentService;
    /** @var IacService */
    private $iacService;

    public function __construct(StudentRepository $studentRepo,
                                GroupRepository $groupRepository,
                                StudentService $studentService,
                                IacService $iacService,
                                SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->studentRepo = $studentRepo;
        $this->groupRepo = $groupRepository;
        $this->studentService = $studentService;
        $this->iacService = $iacService;
    }

    public function index(Request $request)
    {
        if ($request->has('flat')) {
            $field = $request->get('flat');
            $col = $this->studentRepo->flat($field);
            return $this->response($col);
        }
        if ($request->has('group')) {
            $groupId = NtUid::import($request->get('group'));
            $group = $this->groupRepo->get($groupId);
            return $this->response($this->studentRepo->allActiveInGroup($group), ['student_list']);
        }

        return $this->response($this->studentService->all(), ['student_list']);
    }

    public function show($id)
    {
        if (!$id instanceof NtUid) {
            $id = NtUid::import($id);
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

        $data = $request->all();
        $data['auth_token'] = $request->header('Auth');
        $student = $this->studentService->create($data);
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

        $data = $request->all();
        $data['auth_token'] = $request->header('Auth');
        $student = $this->studentService->update($data);
        return $this->response($student, ['student_detail']);
    }

    /* ***************************************************
     * GROUPS
     * **************************************************/
    public function allGroups($id)
    {
        $student = $this->studentService->get($id);
        return $this->response($student->allStudentGroups(), ['student_groups']);
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
        $student = $this->studentService->get($id);

        return $this->response($student->allStudentRedicodi(), ['student_redicodi']);
    }

    public function addRedicodi(Request $request, $id)
    {
        //TODO: validation !
        $data = $request->all();
        $data['auth_token'] = $request->header('Auth');
        $studentRedicodi = $this->studentService->addRedicodi($id, $data);
        return $this->response($studentRedicodi, ['student_redicodi']);
    }

    public function updateRedicodi(Request $request, $studentRedicodiId)
    {
        $data = $request->all();
        $data['auth_token'] = $request->header('Auth');
        $studentRedicodi = $this->studentService->updateRedicodi($studentRedicodiId, $data);
        return $this->response($studentRedicodi, ['student_redicodi']);
    }

    /* ***************************************************
     * IAC
     * **************************************************/
    public function allIac($id)
    {
        $iac = $this->iacService->getIACsForStudent($id, DateRange::infinite());
        return $this->response($iac, ['student_iac']);
    }

    public function addIac(Request $request, $id)
    {
        $data = $request->all();
        $data['auth_token'] = $request->header('Auth');
        $studentIac = $this->iacService->addIac($id, $data);
        return $this->response($studentIac, ['student_iac']);
    }

    public function updateIac(Request $request, $iacId)
    {
        $data = $request->all();
        $data['auth_token'] = $request->header('Auth');
        $studentIac = $this->iacService->updateIac($iacId, $data);
        return $this->response($studentIac, ['student_iac']);
    }

    public function destroyIac($iacId) {
        $this->iacService->deleteIAC($iacId);
        return $this->response(true);
    }
}