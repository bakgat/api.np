<?php
use App\Domain\Model\Identity\Exceptions\GroupNotFoundException;
use App\Domain\Model\Identity\Exceptions\NonUniqueGroupNameException;
use App\Domain\Model\Identity\Exceptions\StaffGroupNotFoundException;
use App\Domain\Model\Identity\Exceptions\StudentGroupNotFoundException;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\GroupRepository;
use App\Domain\Model\Identity\Staff;
use App\Domain\Model\Identity\StaffInGroup;
use App\Domain\Model\Identity\StaffRepository;
use App\Domain\Model\Identity\StaffType;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Identity\StudentInGroup;
use App\Domain\Model\Identity\StudentRepository;
use App\Domain\NtUid;
use App\Repositories\Identity\GroupDoctrineRepository;
use App\Repositories\Identity\StaffDoctrineRepository;
use App\Repositories\Identity\StudentDoctrineRepository;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 23/06/16
 * Time: 16:21
 */
class DoctrineGroupRepositoryTest extends DoctrineTestCase
{
    /** @var GroupRepository */
    protected $groupRepo;
    /** @var  StaffRepository */
    protected $staffRepo;
    /** @var  StudentRepository */
    protected $studentRepo;

    public function setUp()
    {
        parent::setUp();

        $this->groupRepo = new GroupDoctrineRepository($this->em);
        $this->staffRepo = new StaffDoctrineRepository($this->em);
        $this->studentRepo = new StudentDoctrineRepository($this->em);
    }

    /**
     * @test
     * @group group
     * @group grouprepo
     * @group all
     */
    public function should_return_20_active()
    {
        $groups = $this->groupRepo->all();
        $active = $this->groupRepo->allActive();
        $this->assertCount(20, $groups);
        $this->assertCount(20, $active);
    }

    /**
     * @test
     * @group group
     * @group grouprepo
     * @group find
     */
    public function should_find_group_by_its_id()
    {
        $groups = $this->groupRepo->all();
        $id = $groups[0]->getId();

        $this->em->clear();

        $group = $this->groupRepo->find($id);

        $this->assertInstanceOf(Group::class, $group);
        $this->assertEquals($group->getId(), $id);
    }

    /**
     * @test
     * @group group
     * @group grouprepo
     * @group find
     */
    public function should_return_null_when_no_group_found()
    {
        $fakeId = NtUid::generate(4);
        $group = $this->groupRepo->find($fakeId);
        $this->assertNull($group);
    }

    /**
     * @test
     * @group group
     * @group grouprepo
     * @group get
     */
    public function should_get_group_its_id()
    {
        $groups = $this->groupRepo->all();
        $id = $groups[0]->getId();

        $this->em->clear();

        $group = $this->groupRepo->get($id);

        $this->assertInstanceOf(Group::class, $group);
        $this->assertEquals($group->getId(), $id);
    }

    /**
     * @test
     * @group group
     * @group grouprepo
     * @group get
     */
    public function should_throw_when_get_group_fails()
    {
        $this->setExpectedException(GroupNotFoundException::class);
        $fakeId = NtUid::generate(4);
        $group = $this->groupRepo->get($fakeId);
    }

    /**
     * @test
     * @group group
     * @group grouprepo
     * @group insert
     */
    public function should_insert_new_group()
    {
        $name = 'fake_word_unique_' . $this->faker->uuid;

        $group = new Group($name);
        $id = $this->groupRepo->insert($group);

        $this->em->clear();

        $dbGroup = $this->groupRepo->get($id);

        $this->assertInstanceOf(Group::class, $dbGroup);
        $this->assertEquals($dbGroup->getName(), $group->getName());
        $this->assertEquals($dbGroup->getId(), $group->getId());
    }

    /**
     * @test
     * @group group
     * @group grouprepo
     * @group insert
     */
    public function should_throw_not_unique_on_insert()
    {
        $groups = $this->groupRepo->all();
        $group = $groups[0];

        $this->em->clear();

        $non_unique_group = new Group($group->getName());

        $this->setExpectedException(NonUniqueGroupNameException::class);
        $this->groupRepo->insert($non_unique_group);
    }

    /**
     * @test
     * @group group
     * @group grouprepo
     * @group update
     */
    public function should_update_existing_group()
    {
        $name = 'fake_word_unique_' . $this->faker->uuid;

        $group = new Group($name);
        $id = $this->groupRepo->insert($group);

        $this->em->clear();

        $dbGroup = $this->groupRepo->get($id);
        $updateName = $name . $this->faker->uuid;
        $dbGroup->updateName($updateName);
        $count = $this->groupRepo->update($dbGroup);

        $this->em->clear();

        $savedGroup = $this->groupRepo->get($id);

        $this->assertInstanceOf(Group::class, $dbGroup);
        $this->assertInstanceOf(Group::class, $savedGroup);

        $this->assertEquals(1, $count);

        $this->assertNotEquals($group->getName(), $savedGroup->getName());
        $this->assertEquals($group->getId(), $savedGroup->getId());

        $this->assertEquals($dbGroup->getId(), $savedGroup->getId());
        $this->assertEquals($savedGroup->getName(), $updateName);
        $this->assertEquals($dbGroup->getName(), $savedGroup->getName());
    }


    /**
     * @test
     * @group group
     * @group grouprepo
     * @group delete
     */
    public function should_delete_existing_group()
    {
        $name = 'fake_word_unique_' . $this->faker->uuid;

        $group = new Group($name);
        $id = $this->groupRepo->insert($group);

        $this->em->clear();

        $savedGroup = $this->groupRepo->get($id);
        $count = $this->groupRepo->delete($id);

        $this->em->clear();

        $removedGroup = $this->groupRepo->find($id);

        $this->assertEquals($savedGroup->getId(), $id);
        $this->assertEquals(1, $count);
        $this->assertNull($removedGroup);

    }

    /**
     * @test
     * @group group
     * @group student
     * @group grouprepo
     * @group get
     */
    public function should_return_all_active_students()
    {
        $group = $this->getFirstGroup();
        $id = $group->getId();

        $students = $this->groupRepo->allActiveStudents(NtUid::import($id));
        $this->assertGreaterThan(0, count($students));

        /** @var Student $student */
        $student = $students[0];

        $this->assertInstanceOf(Student::class, $student);
        $this->assertGreaterThan(0, $student->getActiveGroups());

        $found = false;
        foreach ($student->getActiveGroups() as $activeGroup) {
            $found = $found || ($activeGroup->getId() == $group->getId());
        }
        $this->assertTrue($found);
    }

    /**
     * @test
     * @group group
     * @group staff
     * @group grouprepo
     * @group get
     */
    public function should_get_staff_group()
    {
        $staff = $this->staffRepo->all();
        /** @var Staff $member */
        $member = $staff[0];

        $staffGroup = $member->allStaffGroups()[0];
        $id = $staffGroup->getId();

        $this->em->clear();

        $dbStaffGroup = $this->groupRepo->getStaffGroup(NtUid::import($id));
        $this->assertInstanceOf(StaffInGroup::class, $dbStaffGroup);
        $this->assertEquals($id, $dbStaffGroup->getId());

    }

    /**
     * @test
     * @group group
     * @group staff
     * @group grouprepo
     * @group get
     */
    public function should_throw_when_no_staff_group_found()
    {
        $this->setExpectedException(StaffGroupNotFoundException::class);
        $fakeId = NtUid::generate(4);
        $this->groupRepo->getStaffGroup(NtUid::import($fakeId));
    }

    /**
     * @test
     * @group group
     * @group staff
     * @group grouprepo
     * @group update
     */
    public function should_update_staff_group()
    {
        $staff = $this->staffRepo->all();
        /** @var Staff $member */
        $member = $staff[0];

        $staffGroup = $member->allStaffGroups()[0];

        $id = $staffGroup->getId();
        $type = $staffGroup->getType();
        $active = $staffGroup->isActive();

        if ($type == StaffType::TITULAR) {
            $staffGroup->changeType(new StaffType(StaffType::TEACHER));
        } else {
            $staffGroup->changeType(new StaffType(StaffType::TITULAR));
        }
        $staffGroup->block();

        $count = $this->groupRepo->updateStaffGroup($staffGroup);

        $this->em->clear();

        $dbStaffGroup = $this->groupRepo->getStaffGroup(NtUid::import($id));

        $this->assertEquals(1, $count);
        $this->assertInstanceOf(StaffInGroup::class, $dbStaffGroup);
        $this->assertEquals($staffGroup->getId(), $dbStaffGroup->getId());

        $this->assertNotEquals($type, $dbStaffGroup->getType());
        $this->assertNotEquals($active, $dbStaffGroup->isActive());

        $this->assertFalse($dbStaffGroup->isActive());
    }

    /**
     * @test
     * @group group
     * @group student
     * @group grouprepo
     * @group get
     */
    public function should_get_student_group()
    {
        /** @var Group $group */
        $group = $this->getFirstGroup();
        $students = $this->studentRepo->allActiveInGroup($group);

        /** @var Student $student */
        $student = $students[0];
        /** @var StudentInGroup $studentGroup */
        $studentGroup = $student->allStudentGroups()[0];
        $id = $studentGroup->getId();

        $this->em->clear();

        $dbStudentGroup = $this->groupRepo->getStudentGroup(NtUid::import($id));
        $this->assertInstanceOf(StudentInGroup::class, $dbStudentGroup);
        $this->assertEquals($id, $dbStudentGroup->getId());
    }

    /**
     * @test
     * @group group
     * @group student
     * @group grouprepo
     * @group update
     */
    public function should_update_student_group()
    {
        /** @var Group $group */
        $group = $this->getFirstGroup();
        $students = $this->studentRepo->allActiveInGroup($group);

        /** @var Student $student */
        $student = $students[0];
        $studentGroup = null;
        /** @var StudentInGroup $studentGroup */
        foreach ($student->allStudentGroups() as $sg) {
            if ($sg->isActive()) {
                $studentGroup = $sg;
                break;
            }
        }
        //should at least have one active group
        $this->assertNotNull($studentGroup);

        $id = $studentGroup->getId();
        $active = $studentGroup->isActive();
        $number = $studentGroup->getNumber();

        $studentGroup->leaveGroup();
        $studentGroup->setNumber(9999);

        $count = $this->groupRepo->updateStudentGroup($studentGroup);

        $this->em->clear();

        $dbStudentGroup = $this->groupRepo->getStudentGroup(NtUid::import($id));

        $this->assertEquals(1, $count);
        $this->assertInstanceOf(StudentInGroup::class, $dbStudentGroup);
        $this->assertEquals($id, $dbStudentGroup->getId());

        $this->assertNotEquals($number, $dbStudentGroup->getNumber());
        $this->assertNotEquals($active, $dbStudentGroup->isActive());

        $this->assertEquals(9999, $dbStudentGroup->getNumber());

        $this->assertFalse($dbStudentGroup->isActive());
    }

    /**
     * @test
     * @group group
     * @group student
     * @group grouprepo
     * @group get
     */
    public
    function should_throw_when_no_student_group_found()
    {
        $this->setExpectedException(StudentGroupNotFoundException::class);
        $fakeId = NtUid::generate(4);
        $this->groupRepo->getStudentGroup(NtUid::import($fakeId));
    }

    /**
     * @return Group
     */
    private
    function getFirstGroup()
    {
        $groups = $this->groupRepo->allActive();
        /** @var Group $group */
        $group = $groups[0];
        return $group;
    }
}
