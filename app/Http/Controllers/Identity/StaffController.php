<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 15/08/16
 * Time: 09:49
 */

namespace App\Http\Controllers\Identity;


use App\Domain\Model\Identity\StaffRepository;
use App\Domain\Model\Identity\StaffType;
use App\Domain\Services\Identity\StaffService;
use App\Domain\Uuid;
use App\Http\Controllers\Controller;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use JMS\Serializer\SerializerInterface;

class StaffController extends Controller
{
    /** @var StaffService */
    protected $staffService;

    public function __construct(StaffService $staffService, SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->staffService = $staffService;
    }

    public function index()
    {
        $result = $this->staffService->all();
        return $this->response($result, ['staff_list']);
    }

    public function show($id)
    {
        $member = $this->staffService->get($id);
        return $this->response($member, ['staff_detail']);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email',
            'gender' => 'required'
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 422);
        }

        $staff = $this->staffService->create($request->all());
        return $this->response($staff, ['staff_detail']);

    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email',
            'gender' => 'required'
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 422);
        }

        $staff = $this->staffService->update($request->all());
        return $this->response($staff, ['staff_detail']);
    }

    public function allTypes()
    {
        $result = new ArrayCollection;
        foreach (StaffType::toArray() as $value=>$key) {
            $result->add(['key'=>$key, 'value'=>$value]);
        }
        return $this->response($result);
    }

    public function allGroups($id)
    {
        $member = $this->staffService->get($id);
        return $this->response($member->getGroups(), ['staff_groups']);
    }

    public function addGroup(Request $request, $id)
    {
        $start = $request->get('start');
        if ($start) {
            $start = $this->toDate($start);
        }
        $end = $request->get('end');
        if ($end) {
            $end = $this->toDate($end);
        }
        $group = $request->get('group');
        $type = $request->get('type');

        $staffGroup = $this->staffService->joinGroup($id, $group['id'], $type, $start, $end);
        return $this->response($staffGroup, ['staff_groups']);
    }

    public function removeGroup(Request $request, $id, $groupId)
    {
        $start = $request->get('start');
        if ($start) {
            $start = $this->toDate($start);
        }
        $end = $request->get('end');
        if ($end) {
            $end = $this->toDate($end);
        }

        $this->staffService->removeFromGroup($id, $groupId);
    }

    public function allRoles($id)
    {
        $member = $this->staffService->get($id);
        return $this->response($member->allStaffRoles(), ['staff_roles']);
    }

    public function addRole(Request $request, $id)
    {
        $start = $request->get('start');
        if ($start) {
            $start = $this->toDate($start);
        }
        $end = $request->get('end');
        if ($end) {
            $end = $this->toDate($end);
        }
        $role = $request->get('role');

        $staffRole = $this->staffService->assignRole($id, $role['id'], $start, $end);
        return $this->response($staffRole, ['staff_roles']);
    }

    public function updateRole(Request $request, $staffRoleId)
    {
        $start = $request->get('start');
        if ($start) {
            $start = $this->toDate($start);
        }
        $end = $request->get('end');
        if ($end) {
            $end = $this->toDate($end);
        }
        $staffRole = $this->staffService->updateRole($staffRoleId, $start, $end);
        return $this->response($staffRole, ['staff_roles']);
    }

    public function removeRole(Request $request, $id, $roleId)
    {
        $end = $request->get('end');
        if ($end) {
            $end = $this->toDate($end);
        }

        return $this->staffService->removeFromRole($id, $roleId, $end);
    }

    private function toDate($sDate)
    {
        $date = strtotime($sDate);
        if ($date != false) {
            $date = new DateTime(date('Y-m-d', $date));
        }
        return $date;
    }

}