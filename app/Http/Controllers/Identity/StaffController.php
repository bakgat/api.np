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
            'gender' => 'required|in:M,F,0'
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
            'gender' => 'required|in:M,F,0'
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
        foreach (StaffType::toArray() as $value => $key) {
            $result->add(['key' => $key, 'value' => $value]);
        }
        return $this->response($result);
    }

    /* ***************************************************
     * GROUPS
     * **************************************************/
    public function allGroups($id)
    {
        $member = $this->staffService->get($id);
        return $this->response($member->allStaffGroups(), ['staff_groups']);
    }

    public function addGroup(Request $request, $id)
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
        $type = $request->get('type');

        $staffGroup = $this->staffService->joinGroup($id, $group['id'], $type, $start, $end);
        return $this->response($staffGroup, ['staff_groups']);
    }

    public function updateGroup(Request $request, $staffGroupId)
    {
        $start = $request->get('start');
        if ($start) {
            $start = convert_date_from_string($start);
        }
        $end = $request->get('end');
        if ($end) {
            $end = convert_date_from_string($end);
        }
        $type = $request->get('type');

        $staffRole = $this->staffService->updateGroup($staffGroupId, $type, $start, $end);
        return $this->response($staffRole, ['staff_groups']);
    }

    public function removeGroup(Request $request, $id, $groupId)
    {
        //TODO: start and end
        $start = $request->get('start');
        if ($start) {
            $start = convert_date_from_string($start);
        }
        $end = $request->get('end');
        if ($end) {
            $end = convert_date_from_string($end);
        }

        $this->staffService->removeFromGroup($id, $groupId);
    }

    /* ***************************************************
     * ROLES
     * **************************************************/
    public function allRoles($id)
    {
        $member = $this->staffService->get($id);
        return $this->response($member->allStaffRoles(), ['staff_roles']);
    }

    public function addRole(Request $request, $id)
    {
        $start = $request->get('start');
        if ($start) {
            $start = convert_date_from_string($start);
        }
        $end = $request->get('end');
        if ($end) {
            $end = convert_date_from_string($end);
        }
        $role = $request->get('role');

        $staffRole = $this->staffService->assignRole($id, $role['id'], $start, $end);
        return $this->response($staffRole, ['staff_roles']);
    }

    public function updateRole(Request $request, $staffRoleId)
    {
        $start = $request->get('start');
        if ($start) {
            $start = convert_date_from_string($start);
        }
        $end = $request->get('end');
        if ($end) {
            $end = convert_date_from_string($end);
        }
        $staffRole = $this->staffService->updateRole($staffRoleId, $start, $end);
        return $this->response($staffRole, ['staff_roles']);
    }

    public function removeRole(Request $request, $id, $roleId)
    {
        $end = $request->get('end');
        if ($end) {
            $end = convert_date_from_string($end);
        }

        return $this->staffService->removeFromRole($id, $roleId, $end);
    }
}