<?php
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\BranchRepository;
use App\Domain\Model\Education\Redicodi;
use App\Domain\Model\Evaluation\RedicodiForStudent;
use App\Domain\Model\Identity\Exceptions\StudentNotFoundException;
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\GroupRepository;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Identity\StudentRepository;
use App\Domain\NtUid;
use App\Repositories\Education\BranchDoctrineRepository;
use App\Repositories\Identity\GroupDoctrineRepository;
use App\Repositories\Identity\StudentDoctrineRepository;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 22/06/16
 * Time: 16:27
 */
class DoctrineStudentRepositoryTest extends DoctrineTestCase
{
    /** @var StudentRepository */
    protected $studentRepo;
    /** @var GroupRepository */
    protected $groupRepo;
    /** @var  BranchRepository */
    protected $branchRepo;

    public function setUp()
    {
        parent::setUp();

        $this->studentRepo = new StudentDoctrineRepository($this->em);
        $this->groupRepo = new GroupDoctrineRepository($this->em);
        $this->branchRepo = new BranchDoctrineRepository($this->em);
    }

    /**
     * @test
     * @group student
     * @group studentrepo
     * @group all
     */
    public function should_return_440_students()
    {
        $students = $this->studentRepo->all();

        $this->assertCount(440, $students);
    }

    /**
     * @test
     * @group student
     * @group studentrepo
     * @group ingroup
     */
    public function should_return_some_students_in_group()
    {
        $group = $this->getFirstGroup();

        $students = $this->studentRepo->allActiveInGroup($group);
        $this->assertGreaterThan(1, count($students));
    }

    /**
     * @test
     * @group student
     * @group studentrepo
     * @group flat
     */
    public function should_return_flat_list()
    {
        //flatten school id because this should be unique
        // and thus returns 440 results
        $students = $this->studentRepo->flat('schoolId');
        $this->assertCount(440, $students);

        $student1 = $students[0];

        $this->assertArrayHasKey('id', $student1);
        $this->assertArrayHasKey('schoolId', $student1);
        $this->assertArrayNotHasKey('displayName', $student1);

    }

    /**
     * @test
     * @group student
     * @group studentrepo
     * @group find
     */
    public function should_find_student_by_its_id()
    {
        $students = $this->studentRepo->all();
        $id = $students[0]->getId();

        $this->em->clear();

        $student = $this->studentRepo->find($id);

        $this->assertInstanceOf(Student::class, $student);
        $this->assertEquals($student->getId(), $id);


    }

    /**
     * @test
     * @group student
     * @group studentrepo
     * @group find
     */
    public function should_return_null_when_no_student_found()
    {
        $fakeId = NtUid::generate(4);
        $student = $this->studentRepo->find($fakeId);
        $this->assertNull($student);
    }

    /**
     * @test
     * @group student
     * @group studentrepo
     * @group get
     */
    public function should_get_student_by_its_id()
    {
        $students = $this->studentRepo->all();
        $id = $students[0]->getId();


        $this->em->clear();

        $student = $this->studentRepo->get($id);

        $this->assertInstanceOf(Student::class, $student);
        $this->assertEquals($student->getId(), $id);
    }

    /**
     * @test
     * @group student
     * @group studentrepo
     * @group get
     */
    public function should_throw_exception_when_get_student_fails()
    {
        $this->setExpectedException(StudentNotFoundException::class);
        $fakeId = NtUid::generate(4);
        $student = $this->studentRepo->get($fakeId);
    }

    /**
     * @test
     * @group student
     * @group studentrepo
     * @group insert
     */
    public function should_insert_new_student()
    {
        $fn = $this->faker->firstName;
        $ln = $this->faker->lastName;
        $schoolId = $this->faker->bankAccountNumber;
        $gender = new Gender($this->faker->randomElement(['F', 'M']));


        $student = new Student($fn, $ln, $schoolId, $gender);
        $id = $this->studentRepo->insert($student);

        $this->em->clear();

        $dbStudent = $this->studentRepo->get($id);


        $this->assertInstanceOf(Student::class, $dbStudent);
        $this->assertEquals($dbStudent->getId(), $student->getId());
        $this->assertEquals($dbStudent->getId(), $id);
        $this->assertEquals($dbStudent->getDisplayName(), $student->getDisplayName());
        $this->assertEquals($dbStudent->getGender(), $student->getGender());
    }

    /**
     * @test
     * @group student
     * @group studentrepo
     * @group update
     */
    public function should_update_existing_student()
    {
        $fn = $this->faker->firstName;
        $ln = $this->faker->lastName;
        $schoolId = $this->faker->bankAccountNumber;
        $gender = new Gender('M');

        $student = new Student($fn, $ln, $schoolId, $gender);
        $id = $this->studentRepo->insert($student);

        $this->em->clear();

        $dbStudent = $this->studentRepo->get($id);

        $dbStudent->updateProfile('Karl', 'Van Iseghem', '0001122', new Gender('F'), new DateTime('1979-11-30'));
        $count = $this->studentRepo->update($dbStudent);

        $this->em->clear();

        $savedStudent = $this->studentRepo->get($id);

        $this->assertInstanceOf(Student::class, $dbStudent);
        $this->assertInstanceOf(Student::class, $savedStudent);

        $this->assertEquals(1, $count);

        $this->assertNotEquals($student->getDisplayName(), $savedStudent->getDisplayName());
        $this->assertNotEquals($student->getBirthday(), $savedStudent->getBirthday());
        $this->assertNotEquals($student->getSchoolId(), $savedStudent->getSchoolId());
        $this->assertNotEquals($student->getGender(), $savedStudent->getGender());

        $this->assertEquals($student->getId(), $savedStudent->getId());

        $this->assertEquals($savedStudent->getDisplayName(), 'Karl Van Iseghem');
        $this->assertEquals($dbStudent->getId(), $savedStudent->getId());
        $this->assertEquals($dbStudent->getDisplayName(), $savedStudent->getDisplayName());
        $this->assertEquals($dbStudent->getBirthday(), $savedStudent->getBirthday());
        $this->assertEquals($dbStudent->getSchoolId(), $savedStudent->getSchoolId());
        $this->assertEquals($dbStudent->getGender(), $savedStudent->getGender());

    }

    /**
     * @test
     * @group student
     * @group studentrepo
     * @group delete
     */
    public function should_delete_existing_student()
    {
        $fn = $this->faker->firstName;
        $ln = $this->faker->lastName;
        $schoolId = $this->faker->bankAccountNumber;
        $gender = new Gender($this->faker->randomElement(['F', 'M']));

        $student = new Student($fn, $ln, $schoolId, $gender);
        $id = $this->studentRepo->insert($student);

        $this->em->clear();

        $savedStudent = $this->studentRepo->get($id);

        $count = $this->studentRepo->delete($id);

        $this->em->clear();

        $removedStudent = $this->studentRepo->find($id);

        $this->assertEquals($savedStudent->getId(), $id);
        $this->assertEquals(1, $count);
        $this->assertNull($removedStudent);
    }

    /**
     * @test
     * @group student
     * @group group
     * @group get
     */
    public function should_have_at_least_two_groups()
    {
        $students = $this->studentRepo->all();
        $student = $students[0];
        $this->assertGreaterThan(1, $student->getGroups());
    }

    /**
     * @test
     * @group student
     * @group group
     * @group update
     */
    public function should_join_a_second_active_group()
    {
        $students = $this->studentRepo->all();
        $student = $students[0];
        $id = $student->getId();

        $groups = $this->groupRepo->all();
        $group = $groups[10];

        $count = count($student->getGroups());
        $this->assertCount(1, $student->getActiveGroups());

        $student->joinGroup($group);
        $this->studentRepo->update($student);

        $this->em->clear();

        $student = $this->studentRepo->get($id);
        $this->assertCount(2, $student->getActiveGroups());
        $this->assertCount($count + 1, $student->getGroups());
    }

    /**
     * @test
     * @group student
     * @group group
     * @group update
     */
    public function should_leave_active_group()
    {
        $students = $this->studentRepo->all();
        $student = $students[0];
        $id = $student->getId();

        $group = $student->getActiveGroups()[0];
        $student->leaveGroup($group);
        $this->studentRepo->update($student);

        $this->em->clear();

        $student = $this->studentRepo->get($id);
        $this->assertCount(0, $student->getActiveGroups());
    }

    /**
     * @return \App\Domain\Model\Identity\Group|mixed|null
     */
    private function getFirstGroup()
    {
        $groups = $this->groupRepo->allActive();
        $group = $groups[0];
        return $group;
    }

    /* ***************************************************
     * REDICODI
     * **************************************************/

    /**
     * @test
     * @group student
     * @group redicodi
     * @group get
     */
    public function should_get_student_redicodi()
    {
        list($student, $redicodi) = $this->getRedicodi();
        $id = $redicodi->getId();

        $this->em->clear();

        $sr = $this->studentRepo->getStudentRedicodi(NtUid::import($id));

        $this->assertInstanceOf(RedicodiForStudent::class, $sr);
        $this->assertEquals($id, $sr->getId());
        $this->assertEquals($student->getId(), $sr->getStudent()->getId());
        $this->assertInstanceOf(Redicodi::class, $sr->getRedicodi());
    }

    /**
     * @test
     * @group student
     * @group redicodi
     * @group update
     */
    public function should_update_student_redicodi()
    {
        /**
         * @var Student $student
         * @var RedicodiForStudent $redicodi
         */
        list($student, $redicodi) = $this->getRedicodi();
        $id = $redicodi->getId();

        $this->em->clear();

        $sr = $this->studentRepo->getStudentRedicodi(NtUid::import($id));

        //Ensure it's a new branch
        $branch = new Branch($this->faker->name);
        $this->branchRepo->insertBranch($branch);

        $newRed = $sr->getRedicodi() == Redicodi::BASIC ?
            new Redicodi(Redicodi::CHALLENGE) :
            new Redicodi(Redicodi::BASIC);
        $content = $this->faker->text(100);

        $sr->update($branch, $newRed, $content);

        $this->studentRepo->updateRedicodi($sr);

        $this->em->clear();

        $dbSR = $this->studentRepo->getStudentRedicodi(NtUid::import($id));
        $this->assertInstanceOf(RedicodiForStudent::class, $dbSR);
        $this->assertEquals($student->getId(), $dbSR->getStudent()->getId());
        $this->assertEquals($branch->getId(), $dbSR->getBranch()->getId());
        $this->assertEquals($content, $dbSR->getContent());
    }

    /**
     * @return array
     */
    private function getRedicodi()
    {
        $students = $this->studentRepo->all();
        $student = null;
        /** @var Student $student */
        foreach ($students as $s) {
            if (count($s->allStudentRedicodi()) > 0) {
                $student = $s;
                break;
            }
        }
        $this->assertNotNull($student);

        /** @var RedicodiForStudent $redicodi */
        $redicodi = $student->allStudentRedicodi()[0];
        return array($student, $redicodi);
    }
}
