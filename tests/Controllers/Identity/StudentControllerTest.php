<?php
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Student;
use App\Domain\Services\Identity\StudentService;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 5/09/16
 * Time: 13:20
 */
class StudentControllerTest extends TestCase
{
    /** @var MockInterface */
    protected $groupRepo;
    /** @var MockInterface */
    protected $studentRepo;
    /** @var StudentService */
    protected $studentService;

    public function setUp()
    {
        parent::setUp();
        $this->studentRepo = $this->mock(App\Domain\Model\Identity\StudentRepository::class);
        $this->groupRepo = $this->mock(App\Domain\Model\Identity\GroupRepository::class);
    }

    /**
     * @test
     * @group StudentController
     */
    public function should_serialize_index()
    {
        $this->studentRepo->shouldReceive('all')
            ->once()
            ->andReturn($this->makeStudentCollection());

        $this->get('students')
            ->seeJsonStructure([
                '*' => [
                    'id',
                    'displayName',
                    'firstName',
                    'lastName',
                    'gender',
                    'birthday',
                    'activeGroups'
                ]
            ]);
    }

    /*
    * PRIVATE METHODS
    */
    private function makeStudentCollection()
    {
        $collection = new ArrayCollection();
        foreach (range(1, 10) as $item) {
            $student = $this->makeStudent();
            $collection->add($student);
        }
        return $collection;
    }

    private function makeStudent()
    {
        $fn = $this->faker->firstName;
        $ln = $this->faker->lastName;
        $schoolId = $this->faker->bankAccountNumber;
        $birthday = $this->faker->dateTime;
        $gender = $this->faker->randomElement(Gender::values());

        $student = new Student($fn, $ln, $schoolId, $gender, $birthday);
        return $student;
    }
}
