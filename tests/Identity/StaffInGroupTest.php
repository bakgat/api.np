<?php
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Staff;
use App\Domain\Model\Identity\StaffInGroup;
use App\Domain\Model\Identity\StaffType;
use App\Domain\Model\Time\DateRange;
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
        $gender = new Gender($this->faker->randomElement(['F', 'M']));

        $staff = new Staff($fn, $ln, $email, $gender);

        $staffInGroup1 = $staff->joinGroup($group1, new StaffType(StaffType::TITULAR));

        $this->assertCount(1, $staff->getGroups());
        $this->assertInstanceOf(Group::class, $staff->getGroups()[0]);

        $staffInGroup2 = $staff->joinGroup($group2, new StaffType(StaffType::TEACHER), $this->faker->dateTimeBetween('-1day', '2years'));
        $this->assertCount(2, $staff->getGroups());
        $this->assertCount(1, $staff->getActiveGroups());

        $this->assertEquals($group1, $staffInGroup1->getGroup());
        $this->assertEquals($staff, $staffInGroup1->getStaff());
        $this->assertEquals($group2, $staffInGroup2->getGroup());
        $this->assertEquals($staff, $staffInGroup2->getStaff());
    }

    /**
     * @test
     * @group staff
     * @group group
     */
    public function should_get_all_staff_groups()
    {
        $group1 = new Group($this->faker->unique()->word());
        $group2 = new Group($this->faker->unique()->word());

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();
        $gender = new Gender($this->faker->randomElement(['F', 'M']));

        $staff = new Staff($fn, $ln, $email, $gender);

        $staff->joinGroup($group1, new StaffType(StaffType::TITULAR));
        $staff->joinGroup($group2, new StaffType(StaffType::TEACHER), $this->faker->dateTimeBetween('-1day', '2years'));

        $this->assertCount(2, $staff->allStaffGroups());
        $this->assertInstanceOf(StaffInGroup::class, $staff->allStaffGroups()[0]);
    }

    /**
     * @test
     * @group staff
     * @group group
     */
    public function should_end_active_group_for_staff()
    {
        $group1 = new Group($this->faker->unique()->word());
        $group2 = new Group($this->faker->unique()->word());

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();
        $gender = new Gender($this->faker->randomElement(['F', 'M']));

        $staff = new Staff($fn, $ln, $email, $gender);

        $staff->joinGroup($group1, new StaffType(StaffType::TITULAR));
        $this->assertCount(1, $staff->getGroups());

        $now = new DateTime();
        $staff->joinGroup($group2, new StaffType(StaffType::TEACHER), null, $now->modify("-1 day"));
        $this->assertCount(2, $staff->getGroups());
        $this->assertCount(1, $staff->getActiveGroups());
    }

    /**
     * @test
     * @group staff
     * @group group
     */
    public function should_been_active_at()
    {
        $group1 = new Group($this->faker->word());
        $group2 = new Group($this->faker->word());

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();
        $gender = new Gender($this->faker->randomElement(['F', 'M']));

        $staff = new Staff($fn, $ln, $email, $gender);

        $nearInfinite = new DateTime('9999-01-01');
        $lowerBound = new DateTime('2014-01-01');
        $upperBound = new DateTime('2016-01-01');

        $staff->joinGroup($group1, new StaffType(StaffType::TITULAR), $lowerBound);
        $staff->joinGroup($group2, new StaffType(StaffType::TEACHER), $lowerBound, $upperBound);

        $this->assertTrue($staff->wasActiveInGroupAt($group1, $nearInfinite));
        $this->assertTrue($staff->wasActiveInGroupAt($group1, $lowerBound));
        $this->assertTrue($staff->wasActiveInGroupAt($group1, $upperBound));

        $this->assertTrue($staff->wasActiveInGroupAt($group2, $lowerBound));
        $this->assertTrue($staff->wasActiveInGroupAt($group2, $upperBound));
        $this->assertFalse($staff->wasActiveInGroupAt($group2, $nearInfinite));
    }

    /**
     * @test
     * @group staff
     * @group group
     */
    public function should_been_active_between()
    {
        $group1 = new Group($this->faker->word());
        $group2 = new Group($this->faker->word());

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();
        $gender = new Gender($this->faker->randomElement(['F', 'M']));

        $staff = new Staff($fn, $ln, $email, $gender);

        $nearInfinite = new DateTime('9999-01-01');
        $lowerBound = new DateTime('2014-01-01');
        $upperBound = new DateTime('2016-01-01');

        $staff->joinGroup($group1, new StaffType(StaffType::TITULAR), $lowerBound);
        $staff->joinGroup($group2, new StaffType(StaffType::TEACHER), $lowerBound, $upperBound);


        $this->assertTrue($staff->wasActiveInGroupBetween($group1, new DateRange($lowerBound, $upperBound)));
        $this->assertTrue($staff->wasActiveInGroupBetween($group1, new DateRange($lowerBound, $nearInfinite)));

        $this->assertTrue($staff->wasActiveInGroupBetween($group2, new DateRange($lowerBound, $upperBound)));
        $this->assertFalse($staff->wasActiveInGroupBetween($group2, new DateRange($lowerBound, $nearInfinite)));
    }

    /**
     * @test
     * @group staff
     * @group group
     */
    public function should_change_type() {
        $group1 = new Group($this->faker->unique()->word());

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();
        $gender = new Gender($this->faker->randomElement(['F', 'M']));

        $staff = new Staff($fn, $ln, $email, $gender);

        $titular = new StaffType(StaffType::TITULAR);
        $teacher = new StaffType(StaffType::TEACHER);

        $staffInGroup1 = $staff->joinGroup($group1, $titular);
        $this->assertEquals($titular, $staffInGroup1->getType());

        $staffInGroup1->changeType($teacher);
        $this->assertEquals($teacher, $staffInGroup1->getType());
    }
}
