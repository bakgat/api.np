<?php
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Staff;
use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 26/06/16
 * Time: 00:04
 */
class StaffInGroupTest extends TestCase
{
    /**
     * @test
     * @group staff
     */
    public function should_add_staff_to_groups()
    {
        $group1 = new Group($this->faker->unique()->word());
        $group2 = new Group($this->faker->unique()->word());

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();

        $staff = new Staff($fn, $ln, $email);

        $staff->joinGroup($group1, 'test');
        $this->assertCount(1, $staff->getGroups());

        $staff->joinGroup($group2, 'test', $this->faker->dateTimeBetween('-1day', '2years'));
        $this->assertCount(2, $staff->getGroups());
        $this->assertCount(1, $staff->getActiveGroups());
    }

    /**
     * @test
     * @group staff
     */
    public function should_end_active_group_for_staff()
    {
        $group1 = new Group($this->faker->unique()->word());
        $group2 = new Group($this->faker->unique()->word());

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();

        $staff = new Staff($fn, $ln, $email);

        $staff->joinGroup($group1, 'test');
        $this->assertCount(1, $staff->getGroups());

        $now = new DateTime();
        $staff->joinGroup($group2, 'test', null, $now->modify("-1 day"));
        $this->assertCount(2, $staff->getGroups());
        $this->assertCount(1, $staff->getActiveGroups());
    }
}
