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
     */
    public function should_have_1_active_group_per_student()
    {
        $students = $this->studentRepo->all();
        foreach ($students as $student) {
            $this->assertCount(1, $student->activeGroups());
        }
    }

    /**
     * @test
     * @group student
     * @group studentrepo
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
     */
    public function should_throw_exception_when_get_student_fails()
    {
        $this->setExpectedException(EntityNotFoundException::class);
        $fakeId = Uuid::generate(4);
        $student = $this->studentRepo->get($fakeId);

    }
}
