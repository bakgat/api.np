<?php
use App\Domain\Services\Identity\StudentService;
use Mockery\MockInterface;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 20/08/16
 * Time: 21:15
 */
class StudentServiceTest extends TestCase
{
    /** @var  StudentService */
    protected $studentService;
    /** @var  MockInterface */
    protected $studentRepo;
    /** @var  MockInterface */
    protected $groupRepo;
    /** @var  MockInterface */
    protected $branchRepo;

    public function setUp() {
        parent::setUp();

        $this->studentRepo = $this->mock(App\Domain\Model\Identity\StudentRepository::class);
        $this->groupRepo = $this->mock(App\Domain\Model\Identity\GroupRepository::class);
        $this->branchRepo = $this->mock(App\Domain\Model\Education\Branch::class);

        $this->studentService = new StudentService($this->studentRepo, $this->groupRepo, $this->branchRepo);
    }

    /**
     * @test
     * @group student
     * @group studentservice
     */
    public function should_create_new() {
        $this->studentRepo->shouldReceive('insert')->once();

        $fn = $this->faker->firstName;
        $ln = $this->faker->lastName;
        $schoolId = $this->faker->bankAccountNumber;
        $birthday = $this->faker->date;
        $gender = 'M';

        $data = [
            'firstName' => $fn,
            'lastName' => $ln,
            'schoolId' => $schoolId,
            'birthday' => $birthday,
            'gender' => $gender
        ];

        $student = $this->studentService->create($data);

        $this->assertEquals($data['firstName'], $student->getFirstName());
        $this->assertEquals($data['lastName'], $student->getLastName());
        $this->assertEquals($data['schoolId'], $student->getSchoolId());
        $this->assertEquals(new DateTime($data['birthday']), $student->getBirthday());
        $this->assertEquals($data['gender'], $student->getGender());
    }
}
