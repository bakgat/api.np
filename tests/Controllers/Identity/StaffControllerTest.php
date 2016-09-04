<?php
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Staff;
use App\Domain\Services\Identity\StaffService;
use Doctrine\Common\Collections\ArrayCollection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Mockery\MockInterface;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 3/09/16
 * Time: 23:13
 */
class StaffControllerTest extends TestCase
{
    /** @var MockInterface */
    protected $groupRepo;
    /** @var MockInterface */
    protected $staffRepo;
    /** @var  MockInterface */
    protected $roleRepo;
    /** @var StaffService */
    protected $staffService;

    public function setUp()
    {
        parent::setUp();
        $this->staffRepo = $this->mock(App\Domain\Model\Identity\StaffRepository::class);
        $this->groupRepo = $this->mock(App\Domain\Model\Identity\GroupRepository::class);
        $this->roleRepo = $this->mock(App\Domain\Model\Identity\RoleRepository::class);

        //$this->staffService = new StaffService($this->staffRepo, $this->groupRepo, $this->roleRepo);
    }

    /**
     * @test
     * @group StaffController
     */
    public function should_serialize_all()
    {
        $this->staffRepo->shouldReceive('all')
            ->once()
            ->andReturn($this->makeStaffCollection());

        $this->get('staff')
            ->seeJsonStructure([
                '*' => [
                    'id',
                    'displayName',
                    'firstName',
                    'lastName',
                    'email',
                    'gender',
                    'birthday'
                ]
            ]);
    }

    /**
     * @test
     * @group StaffController
     */
    public function should_show_and_serialize()
    {
        $staff = $this->makeStaff();

        $this->staffRepo->shouldReceive('get')
            ->once()
            ->andReturn($staff);

        $this->get('staff/' . (string)$staff->getId())
            ->seeJson([
                'id' => (string)$staff->getId(),
                'firstName' => $staff->getFirstName(),
                'lastName' => $staff->getLastName(),
                'email' => $staff->getEmail(),
                'gender' => $staff->getGender(),
                'birthday' => $staff->getBirthday()->format('Y-m-d')
            ])
            ->seeJsonStructure([
                'id',
                'displayName',
                'firstName',
                'lastName',
                'email',
                'gender',
                'birthday',
                'activeGroups'
            ]);
    }

    /**
     * @test
     * @group StaffController
     */
    public function should_store_success()
    {
        $data = [
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName,
            'email' => $this->faker->email,
            'birthday' => $this->faker->date,
            'gender' => 'M'
        ];

        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $this->staffRepo->shouldReceive('insert')
            ->once()
            ->andReturn();

        $this->post('staff', $data)
            ->seeJsonStructure([
                'id',
                'displayName',
                'firstName',
                'lastName',
                'email',
                'gender',
                'birthday'
            ])
            ->seeJson([
                'firstName' => $data['firstName'],
                'lastName' => $data['lastName'],
                'email' => $data['email'],
                'gender' => $data['gender'],
                'birthday' => $data['birthday']
            ]);
    }

    /**
     * @test
     * @group StaffController
     */
    public function should_store_fail()
    {
        $message_bag = new MessageBag(['firstName is required']);
        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => true, 'messages' => $message_bag]));

        $this->post('staff', [])
            ->assertResponseStatus(422);

    }

    /**
     * @test
     * @group StaffController
     */
    public function should_update_existing()
    {
        $staff = $this->makeStaff();
        $newStaff = $this->makeStaff();

        $data = [
            'id' => (string)$staff->getId(),
            'firstName' => $newStaff->getFirstName(),
            'lastName' => $newStaff->getLastName(),
            'email' => $newStaff->getEmail(),
            'birthday' => $newStaff->getBirthday()->format('Y-m-d'),
            'gender' => $newStaff->getGender()
        ];

        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $this->staffRepo->shouldReceive('get')
            ->once()
            ->andReturn($staff);

        $this->staffRepo->shouldReceive('update')
            ->once()
            ->andReturn();

        $this->put('/staff/' . $data['id'], $data)
            ->seeJson([
                'id' => $data['id'],
                'firstName' => $data['firstName'],
                'lastName' => $data['lastName'],
                'email' => $data['email'],
                'gender' => $data['gender'],
                'birthday' => $data['birthday']
            ]);
    }

    /**
     * @test
     * @group StaffController
     */
    public function should_update_fail()
    {
        $message_bag = new MessageBag(['firstName is required']);
        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => true, 'messages' => $message_bag]));

        $fakeId = (string)\App\Domain\Uuid::generate(4);
        $this->put('/staff/' . $fakeId, [])
            ->assertResponseStatus(422);

    }


    private function makeStaffCollection()
    {
        $collection = new ArrayCollection();
        foreach (range(1, 10) as $item) {
            $staff = $this->makeStaff();
            $collection->add($staff);
        }
        return $collection;
    }

    private function makeStaff()
    {
        $fn = $this->faker->firstName;
        $ln = $this->faker->lastName;
        $email = $this->faker->email;
        $birthday = $this->faker->dateTime;
        $gender = $this->faker->randomElement(Gender::values());

        $staff = new Staff($fn, $ln, $email, $gender, $birthday);
        return $staff;
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
