<?php
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Staff;
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

        $staff->joinGroup($group1, new StaffType(StaffType::TITULAR));
        $this->assertCount(1, $staff->getGroups());

        $staff->joinGroup($group2, new StaffType(StaffType::TEACHER), $this->faker->dateTimeBetween('-1day', '2years'));
        $this->assertCount(2, $staff->getGroups());
        $this->assertCount(1, $staff->getActiveGroups());
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

        $staff->joinGroup($group1, new StaffType(StaffType::TITULAR), $lowerBound)
            ->joinGroup($group2, new StaffType(StaffType::TEACHER), $lowerBound, $upperBound);

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

        $staff->joinGroup($group1, new StaffType(StaffType::TITULAR), $lowerBound)
            ->joinGroup($group2, new StaffType(StaffType::TEACHER), $lowerBound, $upperBound);


        $this->assertTrue($staff->wasActiveInGroupBetween($group1, new DateRange($lowerBound, $upperBound)));
        $this->assertTrue($staff->wasActiveInGroupBetween($group1, new DateRange($lowerBound, $nearInfinite)));

        $this->assertTrue($staff->wasActiveInGroupBetween($group2, new DateRange($lowerBound, $upperBound)));
        $this->assertFalse($staff->wasActiveInGroupBetween($group2, new DateRange($lowerBound, $nearInfinite)));
    }
}
