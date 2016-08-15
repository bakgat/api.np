<?php
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Role;
use App\Domain\Model\Identity\Staff;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 15/08/16
 * Time: 09:30
 */
class StaffRoleTest extends TestCase
{
    /**
     * @test
     * @group staff
     * @group role
     * @group staffrole
     */
    public function should_add_staff_to_role() {
        $role1 = new Role($this->faker->unique()->word());
        $group2 = new Role($this->faker->unique()->word());

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();
        $gender = new Gender($this->faker->randomElement(['F', 'M']));

        $staff = new Staff($fn, $ln, $email, $gender);

        $staff->assignRole($role1);
        $this->assertCount(1, $staff->getRoles());

        $staff->assignRole($group2, $this->faker->dateTimeBetween('-1day', '2years'));
        $this->assertCount(2, $staff->getRoles());
        $this->assertCount(1, $staff->getActiveRoles());
    }

    /**
     * @test
     * @group staff
     * @group role
     */
    public function should_end_active_group_for_staff()
    {
        $role1 = new Role($this->faker->unique()->word());
        $role2 = new Role($this->faker->unique()->word());

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();
        $gender = new Gender($this->faker->randomElement(['F', 'M']));

        $staff = new Staff($fn, $ln, $email, $gender);

        $staff->assignRole($role1);
        $this->assertCount(1, $staff->getRoles());

        $now = new DateTime();
        $staff->assignRole($role2, null, $now->modify("-1 day"));
        $this->assertCount(2, $staff->getRoles());
        $this->assertCount(1, $staff->getActiveRoles());
    }


}
