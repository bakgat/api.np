<?php
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Identity\StudentInGroup;
use App\Domain\Services\Identity\StudentService;
use App\Domain\Uuid;
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
        $this->branchRepo = $this->mock(App\Domain\Model\Education\BranchRepository::class);

        $this->studentService = new StudentService($this->studentRepo, $this->groupRepo, $this->branchRepo);
    }

    /**
     * @test
     * @group student
     * @group studentservice
     */
    public function should_create_new() {
        $group = new Group($this->faker->word, true);

        $this->studentRepo->shouldReceive('insert')->once();
        $this->groupRepo->shouldReceive('get')->once()->andReturn($group);

        $fn = $this->faker->firstName;
        $ln = $this->faker->lastName;
        $schoolId = $this->faker->bankAccountNumber;
        $birthday = $this->faker->date;
        $gender = 'M';
        $groupnumber=  $this->faker->biasedNumberBetween(0, 30);

        $data = [
            'firstName' => $fn,
            'lastName' => $ln,
            'schoolId' => $schoolId,
            'birthday' => $birthday,
            'gender' => $gender,
            'group' => [
                'id' => $group->getId()
            ],
            'groupnumber' => $groupnumber
        ];

        $student = $this->studentService->create($data);

        $this->assertEquals($data['firstName'], $student->getFirstName());
        $this->assertEquals($data['lastName'], $student->getLastName());
        $this->assertEquals($data['schoolId'], $student->getSchoolId());
        $this->assertEquals(new DateTime($data['birthday']), $student->getBirthday());
        $this->assertEquals($data['gender'], $student->getGender());
        $this->assertCount(1, $student->getActiveGroups());
    }

    /**
     * @test
     * @group student
     * @group studentservice
     */
    public function should_update_existing() {
        $newStudent = new Student('Karl', 'Van Iseghem', '001001001', new Gender('M'), new DateTime);

        $this->studentRepo->shouldReceive('get')
            ->once()
            ->andReturn($newStudent);

        $this->studentRepo->shouldReceive('update')
            ->once();

        $fn = $this->faker->firstName;
        $ln = $this->faker->lastName;
        $schoolId = $this->faker->bankAccountNumber;
        $birthday = $this->faker->date;
        $gender = 'F';

        $data = [
            'id' => $newStudent->getId(),
            'firstName' => $fn,
            'lastName'=> $ln,
            'schoolId' => $schoolId,
            'birthday' => $birthday,
            'gender'=> $gender,
        ];

        $student = $this->studentService->update($data);

        $this->assertInstanceOf(Student::class, $student);
        $this->assertEquals($data['id'], $student->getId());
        $this->assertEquals($data['firstName'], $student->getFirstName());
        $this->assertEquals($data['lastName'], $student->getLastName());
        $this->assertEquals(new DateTime($data['birthday']), $student->getBirthday());
        $this->assertEquals($data['gender'], $student->getGender());
    }

    /**
     * @test
     * @group student
     * @group studentservice
     */
    public function should_join_group() {
        $group1 = new Group($this->faker->unique(true)->word);
        $number= $this->faker->biasedNumberBetween(1, 30);

        $student = $this->makeStudent();

        $this->studentRepo->shouldReceive('get')
            ->once()
            ->andReturn($student);

        $this->groupRepo->shouldReceive('get')
            ->once()
            ->andReturn($group1);

        $this->studentRepo->shouldReceive('update')
            ->once()
            ->andReturn(1);

        $studentGroup = $this->studentService->joinGroup($student->getId(), $group1->getId(), $number);

        $this->assertInstanceOf(StudentInGroup::class, $studentGroup);
        $this->assertCount(1, $student->getGroups());
    }

    /**
     * @return Student
     */
    private function makeStudent() {
        $fn = $this->faker->firstName;
        $ln = $this->faker->lastName;
        $schoolId = $this->faker->bankAccountNumber;
        $birthday = new DateTime($this->faker->date);

        $gender = $this->faker->randomElement(Gender::values());

        return new Student($fn, $ln, $schoolId, $gender, $birthday);
    }
}
