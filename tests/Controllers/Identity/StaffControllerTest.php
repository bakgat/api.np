<?php
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Role;
use App\Domain\Model\Identity\Staff;
use App\Domain\Model\Identity\StaffType;
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

    /**
     * @test
     * @group StaffController
     */
    public function should_return_all_types()
    {

        $this->get('/staff/types')
            ->seeJsonStructure([
                '*' => [
                    'key', 'value'
                ]
            ]);
    }

    /**
     * @test
     * @group StaffController
     */
    public function should_return_staff_groups()
    {
        $staff = $this->makeStaff();
        $groups = $this->makeGroupCollection();

        $id = (string)$staff->getId();

        foreach ($groups as $group) {
            $staff->joinGroup($group, new StaffType(StaffType::TEACHER));
        }

        $this->staffRepo->shouldReceive('get')
            ->once()
            ->andReturn($staff);

        $this->get('/staff/' . $id . '/groups')
            ->seeJsonStructure([
                '*' => [
                    'id',
                    'group' => [
                        'id',
                        'name'
                    ],
                    'start',
                    'end',
                    'type'
                ]
            ]);
    }

    /**
     * @test
     * @group StaffController
     */
    public function should_add_group_to_staff()
    {
        $now = new DateTime;
        $group = $this->makeGroup();
        $staff = $this->makeStaff();
        $id = (string)$staff->getId();

        $data = [
            'start' => $now->format('Y-m-d'),
            'end' => $now->modify('+1 year')->format('Y-m-d'),
            'group' => ['id' => (string)$group->getId()],
            'type' => StaffType::TITULAR
        ];

        $this->staffRepo->shouldReceive('get')
            ->once()
            ->andReturn($staff);

        $this->groupRepo->shouldReceive('get')
            ->once()
            ->andReturn($group);

        $this->staffRepo->shouldReceive('update')
            ->once()
            ->andReturn(1);

        $this->post('/staff/' . $id . '/groups', $data)
            ->seeJsonStructure([
                'id',
                'group' => [
                    'id',
                    'name'
                ],
                'start',
                'end',
                'type'
            ]);
    }

    /**
     * @test
     * @group StaffController
     */
    public function should_update_staff_group()
    {
        $staff = $this->makeStaff();
        $group = $this->makeGroup();

        $id = (string)$staff->getId();

        $staffGroup = $staff->joinGroup($group, new StaffType(StaffType::TITULAR));
        $staffGroupId = (string)$staffGroup->getId();

        $now = new DateTime;
        $start = clone $now->modify('-1 month');
        $end = clone $now->modify('+1 year');
        $data = [
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
            'group' => ['id' => (string)$group->getId()],
            'type' => StaffType::TEACHER
        ];

        $this->groupRepo->shouldReceive('getStaffGroup')
            ->once()
            ->andReturn($staffGroup);
        $this->groupRepo->shouldReceive('updateStaffGroup')
            ->once()
            ->andReturn(1);

        $this->put('/staff/' . $id . '/groups/' . $staffGroupId, $data)
            ->seeJson([
                'id' => $staffGroupId,
                'group' => [
                    'id' => (string)$group->getId(),
                    'name' => $group->getName()
                ],
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
                'type' => $data['type']
            ]);
    }

    /**
     * @test
     * @group StaffController
     */
    public function should_return_staff_roles()
    {
        $staff = $this->makeStaff();
        $roles = $this->makeRoleCollection();
        $id = (string)$staff->getId();

        foreach ($roles as $role) {
            $staff->assignRole($role);
        }

        $this->staffRepo->shouldReceive('get')
            ->once()
            ->andReturn($staff);

        $this->get('/staff/' . $id . '/roles')
            ->seeJsonStructure([
                '*' => [
                    'id',
                    'role' => [
                        'id',
                        'name'
                    ],
                    'start',
                    'end'
                ]
            ]);
    }

    /*
     * PRIVATE METHODS
     */
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

    /**
     * @return Role
     */
    private function makeRole()
    {
        $role = new Role($this->faker->unique()->word);
        return $role;
    }

    /**
     * @return ArrayCollection
     */
    private function makeRoleCollection()
    {
        $collection = new ArrayCollection();
        foreach (range(1, 5) as $item) {
            $role = $this->makeRole();
            $collection->add($role);
        }
        return $collection;
    }


}
