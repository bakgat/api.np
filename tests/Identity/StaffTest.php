<?php
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Staff;
use App\Domain\Model\Identity\StaffType;
use Webpatser\Uuid\Uuid;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 25/06/16
 * Time: 23:08
 */
class StaffTest extends TestCase
{
    /**
     * @test
     * @group staff
     */
    public function should_create_new()
    {
        $fn = $this->faker->firstName;
        $ln = $this->faker->lastName;
        $email = $this->faker->email;
        $gender = new Gender($this->faker->randomElement(['F', 'M']));

        $staff = new Staff($fn, $ln, $email, $gender);

        $this->assertInstanceOf(Uuid::class, $staff->getId());
        $this->assertCount(5, explode('-', $staff->getId()));
        $this->assertEquals($staff->getDisplayName(), $fn . ' ' . $ln);
        $this->assertEquals($staff->getEmail(), $email);
        $this->assertEquals($staff->getGender(), $gender);
    }
}
