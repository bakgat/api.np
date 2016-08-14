<?php
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Role;
use App\Domain\Model\Identity\Staff;
use App\Domain\Uuid;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 13/08/16
 * Time: 20:27
 */
class RoleTest extends TestCase
{
    /**
     * @test
     * @group role
     */
    public function should_create_new()
    {
        $name = $this->faker->word();

        $role = new Role($name);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertInstanceOf(Uuid::class, $role->getId());
        $this->assertEquals($name, $role->getName());
    }

    /**
     * @test
     * @group role
     * @group staff
     */
    public function should_add_user_to_role()
    {
        $name = $this->faker->word();
        $role = new Role($name);

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();
        $gender = new Gender(Gender::MALE);

        $staff = new Staff($fn, $ln, $email, $gender);
        $staff->assignRole($role);

        $this->assertCount(1, $staff->getRoles());
        $this->assertInstanceOf(Role::class, $staff->getRoles()[0]);

    }
}
