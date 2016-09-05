<?php
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Group;
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
                'id' =>  $id,
                'displayName' => $student->getDisplayName(),
                'firstName' => $student->getFirstName(),
                'lastName' => $student->getLastName(),
                'schoolId' => $student->getSchoolId(),
                'gender' => $student->getGender(),
                'birthday' => $student->getBirthday()->format('Y-m-d'),
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
