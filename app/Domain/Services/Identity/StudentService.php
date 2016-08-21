<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 20/08/16
 * Time: 20:25
 */

namespace App\Domain\Services\Identity;


use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\GroupRepository;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Identity\StudentInGroup;
use App\Domain\Model\Identity\StudentRepository;
use App\Domain\Uuid;

class StudentService
{
    /** @var StudentRepository */
    protected $studentRepo;
    /** @var GroupRepository */
    protected $groupRepo;

    public function __construct(StudentRepository $studentRepository, GroupRepository $groupRepository)
    {
        $this->studentRepo = $studentRepository;
        $this->groupRepo = $groupRepository;
    }

    public function all()
    {
        return $this->studentRepo->all();
    }

    public function get($id)
    {
        if (!$id instanceof Uuid) {
            $id = Uuid::import($id);
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
        $group = $this->groupRepo->get(Uuid::import($groupId));
        $groupNumber = $data['groupnumber'];

        $student = new Student($firstName, $lastName, $schoolId, $gender, $birthday);
        $student->joinGroup($group, $groupNumber);

        $this->studentRepo->insert($student);

        return $student;
    }

    public function update($data)
    {
        $id = Uuid::import($data['id']);
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
    public function joinGroup($id, $groupId, $number, $start, $end)
    {
        /** @var Student $student */
        $student = $this->get(Uuid::import($id));
        /** @var Group $group */
        $group = $this->groupRepo->get(Uuid::import($groupId));
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
        $studentGroup = $this->groupRepo->getStudentGroup(Uuid::import($studentGroupId));

        $studentGroup->resetStart($start);
        if ($end != null) {
            $studentGroup->block($end);
        }
        $studentGroup->setNumber($number);
        $this->groupRepo->updateStudentGroup($studentGroup);

        return $studentGroup;
    }


}