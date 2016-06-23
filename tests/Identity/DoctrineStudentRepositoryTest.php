<?php
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Identity\StudentRepository;
use App\Repositories\Identity\DoctrineStudentRepository;
use Doctrine\ORM\EntityNotFoundException;
use Webpatser\Uuid\Uuid;


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

    public function setUp()
    {
        parent::setUp();

        $this->studentRepo = new DoctrineStudentRepository($this->em);
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
        $fakeId = Uuid::generate(4);
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
        $this->setExpectedException(EntityNotFoundException::class);
        $fakeId = Uuid::generate(4);
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
        $email = $this->faker->email;

        $student = new Student($fn, $ln, $email);
        $id = $this->studentRepo->insert($student);

        $this->em->clear();

        $dbStudent = $this->studentRepo->get($id);


        $this->assertInstanceOf(Student::class, $dbStudent);
        $this->assertEquals($dbStudent->getId(), $student->getId());
        $this->assertEquals($dbStudent->getId(), $id);
        $this->assertEquals($dbStudent->getDisplayName(), $student->getDisplayName());
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
        $email = $this->faker->email;

        $student = new Student($fn, $ln, $email);
        $id = $this->studentRepo->insert($student);

        $this->em->clear();

        $dbStudent = $this->studentRepo->get($id);
        $dbStudent->updateProfile('Karl', 'Van Iseghem', 'karl.vaniseghem@klimtoren.be', new DateTime('1979-11-30'));
        $count = $this->studentRepo->update($dbStudent);

        $this->em->clear();

        $savedStudent = $this->studentRepo->get($id);

        $this->assertInstanceOf(Student::class, $dbStudent);
        $this->assertInstanceOf(Student::class, $savedStudent);

        $this->assertEquals(1, $count);

        $this->assertNotEquals($student->getDisplayName(), $savedStudent->getDisplayName());
        $this->assertNotEquals($student->getBirthday(), $savedStudent->getBirthday());
        $this->assertNotEquals($student->getEmail(), $savedStudent->getEmail());

        $this->assertEquals($student->getId(), $savedStudent->getId());

        $this->assertEquals($savedStudent->getDisplayName(), 'Karl Van Iseghem');
        $this->assertEquals($dbStudent->getId(), $savedStudent->getId());
        $this->assertEquals($dbStudent->getDisplayName(), $savedStudent->getDisplayName());
        $this->assertEquals($dbStudent->getBirthday(), $savedStudent->getBirthday());
        $this->assertEquals($dbStudent->getEmail(), $savedStudent->getEmail());

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
        $email = $this->faker->email;

        $student = new Student($fn, $ln, $email);
        $id = $this->studentRepo->insert($student);

        $this->em->clear();

        $savedStudent = $this->studentRepo->find($id);

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
        $id = $students[0]->getId();

        $student = $this->studentRepo->get($id);
        $this->assertGreaterThan(1, $student->getGroups());
    }
}
