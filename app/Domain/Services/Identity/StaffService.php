<?php

namespace App\Domain\Services\Identity;

use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\GroupRepository;
use App\Domain\Model\Identity\Role;
use App\Domain\Model\Identity\RoleRepository;
use App\Domain\Model\Identity\Staff;
use App\Domain\Model\Identity\StaffInGroup;
use App\Domain\Model\Identity\StaffRepository;
use App\Domain\Model\Identity\StaffRole;
use App\Domain\Model\Identity\StaffType;
use App\Domain\Model\Time\DateRange;
use App\Domain\Uuid;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 17/08/16
 * Time: 22:17
 */
class StaffService
{
    /** @var  StaffRepository */
    protected $staffRepo;
    /** @var GroupRepository */
    protected $groupRepo;
    /** @var RoleRepository */
    protected $roleRepo;

    public function __construct(StaffRepository $staffRepository, GroupRepository $groupRepository, RoleRepository $roleRepository)
    {
        $this->staffRepo = $staffRepository;
        $this->groupRepo = $groupRepository;
        $this->roleRepo = $roleRepository;
    }

    public function all()
    {
        return $this->staffRepo->all();
    }

    /**
     * @param $id
     * @return Staff
     */
    public function get($id)
    {
        if (!$id instanceof Uuid) {
            $id = Uuid::import($id);
        }
        $member = $this->staffRepo->get($id);
        return $member;
    }

    public function create($data)
    {
        $firstName = $data['firstName'];
        $lastName = $data['lastName'];
        $email = $data['email'];
        $birthday = null;
        if (isset($data['birthday'])) {
            $birthday = strtotime($data['birthday']);
            if ($birthday !== false) {
                $birthday = new DateTime(date('Y-m-d', $birthday));
            }
        }
        $gender = new Gender($data['gender']);

        $staff = new Staff($firstName, $lastName, $email, $gender, $birthday);
        $this->staffRepo->insert($staff);

        return $staff;
    }

    public function update($data)
    {
        $id = Uuid::import($data['id']);
        $staff = $this->get($id);

        $firstName = $data['firstName'];
        $lastName = $data['lastName'];
        $email = $data['email'];
        $birthday = null;
        if (isset($data['birthday'])) {
            $birthday = strtotime($data['birthday']);
            if ($birthday !== false) {
                $birthday = new DateTime(date('Y-m-d', $birthday));
            }
        }
        $gender = new Gender($data['gender']);

        $staff->updateProfile($firstName, $lastName, $email, $gender, $birthday);
        $this->staffRepo->update($staff);

        return $staff;
    }

    /* -------------------------------------------
    * GROUP FUNCTIONS
    * ----------------------------------------- */

    /**
     * @param $id
     * @param $groupId
     * @param $type
     * @param DateTime|null $start
     * @param DateTime|null $end
     * @return StaffInGroup|null
     */
    public function joinGroup($id, $groupId, $type, $start, $end)
    {
        /** @var Staff $member */
        $member = $this->get(Uuid::import($id));
        /** @var Group $group */
        $group = $this->groupRepo->get(Uuid::import($groupId));

        /** @var StaffInGroup $staffGroup */
        $staffGroup = null;
        if ($member && $group) {
            $type = new StaffType($type);
            $staffGroup = $member->joinGroup($group, $type, $start, $end);
        }
        $this->staffRepo->update($member);
        return $staffGroup;
    }

    public function updateGroup($staffGroupId, $type, $start, $end)
    {
        /** @var StaffInGroup $group */
        $staffGroup = $this->groupRepo->getStaffGroup(Uuid::import($staffGroupId));
        $staffGroup->resetStart($start);
        if($end != null) {
            $staffGroup->block($end);
        }
        if(!$type instanceof StaffType) {
            $type = new StaffType($type);
        }
        $staffGroup->changeType($type);
        $this->groupRepo->updateStaffGroup($staffGroup);
        return $staffGroup;
    }

    public function removeFromGroup($id, $groupId)
    {
        /** @var Staff $member */
        $member = $this->get($id);
        /** @var Group $group */
        $group = $this->groupRepo->get($groupId);

    }

    /* -------------------------------------------
     * ROLE FUNCTIONS
     * ----------------------------------------- */

    /**
     * Assigns a role to a given staff member.
     *
     * @param $id
     * @param $roleId
     * @param DateTime|null $start
     * @param DateTime|null $end
     * @return StaffRole|null
     */
    public function assignRole($id, $roleId, $start = null, $end = null)
    {
        /** @var Staff $member */
        $member = $this->get(Uuid::import($id));
        /** @var Role $role */
        $role = $this->roleRepo->get(Uuid::import($roleId));

        $staffRole = null;
        if ($member && $role) {
            $staffRole = $member->assignRole($role, $start, $end);
        }
        $this->staffRepo->update($member);
        return $staffRole;
    }

    public function updateRole($staffRoleId, $start = null, $end = null)
    {
        /** @var StaffRole $staffRole */
        $staffRole = $this->roleRepo->getStaffRole(Uuid::import($staffRoleId));
        $staffRole->resetStart($start);
        if ($end != null) {
            $staffRole->block($end);
        }
        $this->roleRepo->updateStaffRole($staffRole);
        return $staffRole;
    }

    /**
     * Removes an active role for a given staff member.
     *
     * @param $id
     * @param $roleId
     * @return bool True if succeeded.
     */
    public function removeFromRole($id, $roleId, $end)
    {
        /** @var Staff $member */
        $member = $this->get($id);
        /** @var Role $role */
        $role = $this->roleRepo->get($roleId);

        if ($member && $role) {
            $member->removeRole($role, $end);
        }
        $this->staffRepo->update($member);
        return true;
    }


}