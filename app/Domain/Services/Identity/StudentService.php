<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 20/08/16
 * Time: 20:25
 */

namespace App\Domain\Services\Identity;


use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\BranchRepository;
use App\Domain\Model\Education\Redicodi;
use App\Domain\Model\Evaluation\RedicodiForStudent;
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\GroupRepository;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Identity\StudentInGroup;
use App\Domain\Model\Identity\StudentRepository;
use App\Domain\NtUid;

class StudentService
{
    /** @var StudentRepository */
    protected $studentRepo;
    /** @var GroupRepository */
    protected $groupRepo;
    /** @var BranchRepository */
    protected $branchRepo;

    public function __construct(StudentRepository $studentRepository, GroupRepository $groupRepository, BranchRepository $branchRepository)
    {
        $this->studentRepo = $studentRepository;
        $this->groupRepo = $groupRepository;
        $this->branchRepo = $branchRepository;
    }

    public function all()
    {
        return $this->studentRepo->all();
    }

    public function get($id)
    {
        if (!$id instanceof NtUid) {
            $id = NtUid::import($id);
        }
        $student = $this->studentRepo->get($id);
        return $student;
    }

    public function create($data)
    {
        $firstName = $data['firstName'];
        $lastName = $data['lastName'];
        $birthday = null;
        if (isset($data['birthday'])) {
            $birthday = convert_date_from_string($data['birthday']);
        }
        $gender = new Gender($data['gender']);
        $schoolId = $data['schoolId'];

        $groupId = $data['group']['id'];
        $group = $this->groupRepo->get(NtUid::import($groupId));
        $groupNumber = $data['groupnumber'];

        $student = new Student($firstName, $lastName, $schoolId, $gender, $birthday);
        $student->joinGroup($group, $groupNumber);

        $this->studentRepo->insert($student);

        return $student;
    }

    public function update($data)
    {
        $id = NtUid::import($data['id']);
        $student = $this->get($id);

        $firstName = $data['firstName'];
        $lastName = $data['lastName'];
        $birthday = null;
        if (isset($data['birthday'])) {
            $birthday = convert_date_from_string($data['birthday']);
        }
        $gender = new Gender($data['gender']);
        $schoolId = $data['schoolId'];

        $student->updateProfile($firstName, $lastName, $schoolId, $gender, $birthday);
        $this->studentRepo->update($student);

        return $student;
    }

    /* ***************************************************
     * GROUPS
     * **************************************************/
    public function joinGroup($id, $groupId, $number, $start = null, $end = null)
    {
        /** @var Student $student */
        $student = $this->get(NtUid::import($id));
        /** @var Group $group */
        $group = $this->groupRepo->get(NtUid::import($groupId));
        /** @var StudentInGroup $studentGroup */
        $studentGroup = null;
        if ($student && $group) {
            $studentGroup = $student->joinGroup($group, $number, $start, $end);
        }
        $this->studentRepo->update($student);
        return $studentGroup;
    }

    public function updateGroup($studentGroupId, $number, $start, $end)
    {
        $studentGroup = $this->groupRepo->getStudentGroup(NtUid::import($studentGroupId));

        $studentGroup->resetStart($start);
        if ($end != null) {
            $studentGroup->block($end);
        }
        $studentGroup->setNumber($number);
        $this->groupRepo->updateStudentGroup($studentGroup);

        return $studentGroup;
    }

    /* ***************************************************
     * REDICODI
     * **************************************************/
    public function addRedicodi($id, $branchId, $redicodi, $content, $start, $end)
    {
        /** @var Student $student */
        $student = $this->get(NtUid::import($id));
        /** @var Branch $branch */
        $branch = null;
        if ($branchId != null) {
            $branch = $this->branchRepo->getBranch(NtUid::import($branchId));
        }

        /** @var RedicodiForStudent $studentRedicodi */
        $studentRedicodi = null;
        if ($student) {
            $redicodi = new Redicodi($redicodi);
            $studentRedicodi = $student->addRedicodi($redicodi, $branch, $content, $start, $end);
        }
        $this->studentRepo->update($student);
        return $studentRedicodi;
    }

    public function updateRedicodi($studentRedicodiId, $branchId, $redicodi, $content, $start, $end)
    {
        /** @var RedicodiForStudent $studentRedicodi */
        $studentRedicodi = $this->studentRepo->getStudentRedicodi(NtUid::import($studentRedicodiId));
        /** @var Branch $branch */
        $branch = null;
        if ($branchId != null) {
            $branch = $this->branchRepo->getBranch(NtUid::import($branchId));
        }

        $studentRedicodi->resetStart($start);
        if ($end != null) {
            $studentRedicodi->stopRedicodi($end);
        }

        $redicodi = new Redicodi($redicodi);
        $studentRedicodi->update($branch, $redicodi, $content);
        $this->studentRepo->updateRedicodi($studentRedicodi);
        return $studentRedicodi;
    }


}