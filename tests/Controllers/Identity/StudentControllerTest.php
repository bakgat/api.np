<?php
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Student;
use App\Domain\Services\Identity\StudentService;
use Doctrine\Common\Collections\ArrayCollection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
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

    /**
     * @test
     * @group StudentController
     */
    public function should_serialize_flat_index()
    {
        $collection = $this->makeStudentCollection();
        $flat_col = new ArrayCollection;
        /** @var Student $item */
        foreach ($collection as $item) {
            $flat_col->add([
                'id' => (string)$item->getId(),
                'firstName' => $item->getFirstName()
            ]);
        }

        $this->studentRepo->shouldReceive('flat')
            ->once()
            ->andReturn($flat_col);

        $this->get('students?flat=firstName')
            ->seeJsonStructure([
                '*' => [
                    'id',
                    'firstName'
                ]
            ]);
    }

    /**
     * @test
     * @group StudentController
     */
    public function should_serialize_student_in_group_index()
    {
        $collection = $this->makeStudentCollection();

        $group = $this->makeGroup();
        $groupId = (string)$group->getId();

        $this->groupRepo->shouldReceive('get')
            ->once()
            ->andReturn($group);

        $this->studentRepo->shouldReceive('allActiveInGroup')
            ->once()
            ->andReturn($collection);

        $this->get('students?group=' . $groupId)
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

    /**
     * @test
     * @group StudentController
     */
    public function should_serialize_show()
    {
        $student = $this->makeStudent();
        $id = (string)$student->getId();

        $this->studentRepo->shouldReceive('find')
            ->once()
            ->andReturn($student);

        $this->get('students/' . $id)
            ->seeJson([
                'id' => $id,
                'displayName' => $student->getDisplayName(),
                'firstName' => $student->getFirstName(),
                'lastName' => $student->getLastName(),
                'schoolId' => $student->getSchoolId(),
                'gender' => $student->getGender(),
                'birthday' => $student->getBirthday()->format('Y-m-d'),
            ]);
    }

    /**
     * @test
     * @group StudentController
     */
    public function should_store_success()
    {
        $group = $this->makeGroup();
        $data = [
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName,
            'schoolId' => $this->faker->bankAccountNumber,
            'birthday' => $this->faker->date,
            'gender' => 'M',
            'group' => ['id' => (string)$group->getId()],
            'groupnumber' => $this->faker->biasedNumberBetween(1, 30),
        ];

        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $this->groupRepo->shouldReceive('get')
            ->once()
            ->andReturn($group);

        $this->studentRepo->shouldReceive('insert')
            ->once()
            ->andReturn();

        $this->post('students', $data)
            ->seeJsonStructure([
                'id',
                'displayName',
                'firstName',
                'lastName',
                'schoolId',
                'gender',
                'birthday',
                'activeGroups' => [
                    '*' => [
                        'id',
                        'name'
                    ]
                ]
            ])
            ->seeJson([
                'firstName' => $data['firstName'],
                'lastName' => $data['lastName'],
                'schoolId' => $data['schoolId'],
                'gender' => $data['gender'],
                'birthday' => $data['birthday'],
                'activeGroups' => [
                    [
                        'id' => (string)$group->getId(),
                        'name' => $group->getName()
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @group StudentController
     */
    public function should_store_fail()
    {
        $message_bag = new MessageBag(['firstName is required']);
        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => true, 'messages' => $message_bag]));

        $this->post('students', [])
            ->assertResponseStatus(422);
    }

    /**
     * @test
     * @group StudentController
     */
    public function should_update_existing()
    {
        $student = $this->makeStudent();
        $newStudent = $this->makeStudent();

        $data = [
            'id' => (string)$student->getId(),
            'firstName' => $newStudent->getFirstName(),
            'lastName' => $newStudent->getLastName(),
            'schoolId' => $newStudent->getSchoolId(),
            'gender' => $newStudent->getGender(),
            'birthday' => $newStudent->getBirthday()->format('Y-m-d'),
        ];

        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $this->studentRepo->shouldReceive('get')
            ->once()
            ->andReturn($student);

        $this->studentRepo->shouldReceive('update')
            ->once()
            ->andReturn(1);

        $this->put('students/' . $data['id'], $data)
            ->seeJson([
                'id' => $data['id'],
                'firstName' => $data['firstName'],
                'lastName' => $data['lastName'],
                'schoolId' => $data['schoolId'],
                'gender' => $data['gender'],
                'birthday' => $data['birthday']
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

    /**
     * @return ArrayCollection
     */
    private function makeGroupCollection()
    {
        $collection = new ArrayCollection();
        foreach (range(1, 10) as $item) {
            $group = $this->makeGroup();
            $collection->add($group);
        }
        return $collection;
    }

    /**
     * @return Group
     */
    private function makeGroup()
    {
        $group = new Group($this->faker->word);
        return $group;
    }
}
