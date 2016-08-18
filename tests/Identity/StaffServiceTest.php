<?php
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Staff;
use App\Domain\Services\Identity\StaffService;
use App\Domain\Uuid;
use Mockery\MockInterface;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 18/08/16
 * Time: 15:24
 */
class StaffServiceTest extends TestCase
{
    /** @var StaffService */
    protected $service;
    /** @var MockInterface */
    protected $staffRepo;

    public function setUp()
    {
        parent::setUp();
        $this->staffRepo = $this->mock(App\Domain\Model\Identity\StaffRepository::class);
        $this->service = new StaffService($this->staffRepo);
    }


    /**
     * @test
     * @group staff
     * @group staffservice
     */
    public function should_create_new()
    {
        $this->staffRepo->shouldReceive('insert')->once();


        $fn = $this->faker->firstName;
        $ln = $this->faker->lastName;
        $email = $this->faker->email;
        $birthday = $this->faker->date;
        $gender = 'M';

        $data = [
            'firstName' => $fn,
            'lastName' => $ln,
            'email' => $email,
            'birthday' => $birthday,
            'gender' => $gender
        ];


        $staff = $this->service->create($data);

        $this->assertEquals($data['firstName'], $staff->getFirstName());
        $this->assertEquals($data['lastName'], $staff->getLastName());
        $this->assertEquals($data['email'], $staff->getEmail());
        $this->assertEquals(new DateTime($data['birthday']), $staff->getBirthday());
        $this->assertEquals($data['gender'], $staff->getGender());
    }

    /**
     * @test
     * @group staff
     * @group staffservice
     */
    public function should_update_existing_staff() {

        /** @var Staff $newStaff */
        $newStaff = new Staff('Karl', 'Van Iseghem', 'karl.vaniseghem@klimtoren.be',
            new Gender('M'));


        $this->staffRepo->shouldReceive('get')
            ->once()
            ->andReturn($newStaff);

        $this->staffRepo->shouldReceive('update')
            ->once();

        $fn = $this->faker->firstName;
        $ln = $this->faker->lastName;
        $email = $this->faker->email;
        $birthday = $this->faker->date;
        $gender = 'M';

        $data = [
            'id' => $newStaff->getId(),
            'firstName' => $fn,
            'lastName' => $ln,
            'email' => $email,
            'birthday' => $birthday,
            'gender' => $gender
        ];


        $staff = $this->service->update($data);

        $this->assertEquals($data['id'], $staff->getId());
        $this->assertEquals($data['firstName'], $staff->getFirstName());
        $this->assertEquals($data['lastName'], $staff->getLastName());
        $this->assertEquals($data['email'], $staff->getEmail());
        $this->assertEquals(new DateTime($data['birthday']), $staff->getBirthday());
        $this->assertEquals($data['gender'], $staff->getGender());
    }
}
