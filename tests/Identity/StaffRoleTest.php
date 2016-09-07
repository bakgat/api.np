<?php
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Role;
use App\Domain\Model\Identity\Staff;
use App\Domain\Model\Identity\StaffRole;
use App\Domain\Model\Time\DateRange;
use App\Domain\NtUid;

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
    public function should_add_staff_to_role()
    {
        $role1 = new Role($this->faker->unique()->word());
        $group2 = new Role($this->faker->unique()->word());

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();
        $gender = new Gender($this->faker->randomElement(['F', 'M']));

        $staff = new Staff($fn, $ln, $email, $gender);

        $staffRole = $staff->assignRole($role1);
        $this->assertCount(1, $staff->getRoles());

        $staff->assignRole($group2, $this->faker->dateTimeBetween('-1day', '2years'));
        $this->assertCount(2, $staff->getRoles());
        $this->assertCount(1, $staff->getActiveRoles());

        $this->assertInstanceOf(NtUid::class, $staffRole->getId());
        $this->assertEquals($staff, $staffRole->getStaff());
        $this->assertEquals($role1, $staffRole->getRole());
        $now = new DateTime;

        $this->assertEquals($now->format('Y-m-d'), $staffRole->isActiveSince()->format('Y-m-d'));
        $this->assertEquals(DateRange::FUTURE, $staffRole->isActiveUntil()->format('Y-m-d'));
    }

    /**
     * @test
     * @group staff
     * @group role
     */
    public function should_end_active_group_for_staff()
    {
        $role1 = new Role($this->faker->unique(true)->word);
        $role2 = new Role($this->faker->unique()->word);

        $staff = $this->makeStaff();

        $staff->assignRole($role1);
        $this->assertCount(1, $staff->getRoles());

        $now = new DateTime();
        $staff->assignRole($role2, null, $now->modify("-1 day"));
        $this->assertCount(2, $staff->getRoles());
        $this->assertCount(1, $staff->getActiveRoles());
    }

    /**
     * @test
     * @group staff
     * @group role
     */
    public function should_reset_start_for_staffrole()
    {
        $role1 = new Role($this->faker->unique(true)->word);

        $staff = $this->makeStaff();

        $now = new DateTime();
        $staffRole = $staff->assignRole($role1, $now);

        $this->assertTrue($staffRole->isActive());

        $twoDays = $now->modify("-2 days");
        $staffRole->resetStart($twoDays);
        $this->assertTrue($staffRole->isActive());
        $this->assertEquals($twoDays->format('Y-M-d'), $staffRole->isActiveSince()->format('Y-M-d'));
    }
    /**
     * @test
     * @group staff
     * @group role
     */
    public function should_block_staffrole()
    {
        $role1 = new Role($this->faker->unique(true)->word);

        $staff = $this->makeStaff();

        $now = new DateTime();
        $staffRole = $staff->assignRole($role1, $now);

        $this->assertTrue($staffRole->isActive());

        $staffRole->block();
        $this->assertFalse($staffRole->isActive());
        $this->assertEquals($now->modify("-1 day")->format('Y-M-d'), $staffRole->isActiveUntil()->format('Y-M-d'));
    }

    /**
     * @test
     * @group staff
     * @group role
     */
    public function should_get_all_staff_roles()
    {
        $role1 = new Role($this->faker->unique()->word());
        $role2 = new Role($this->faker->unique()->word());

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();
        $gender = new Gender($this->faker->randomElement(['F', 'M']));

        $staff = new Staff($fn, $ln, $email, $gender);

        $staff->assignRole($role1);

        $now = new DateTime();
        $staff->assignRole($role2, null, $now->modify("-1 day"));


        $this->assertCount(2, $staff->allStaffRoles());
        $this->assertInstanceOf(StaffRole::class, $staff->allStaffRoles()[0]);
    }

    private function makeStaff() {
        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();
        $gender = new Gender($this->faker->randomElement(['F', 'M']));

        $staff = new Staff($fn, $ln, $email, $gender);

        return $staff;
    }
}
