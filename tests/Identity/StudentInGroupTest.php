<?php
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Time\DateRange;
use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 21/06/16
 * Time: 15:21
 */
class StudentInGroupTest extends TestCase
{




    /**
     * @test
     * @group group student
     */
    public function should_add_student_to_groups()
    {
        $group1 = new Group($this->faker->word());
        $group2 = new Group($this->faker->word());

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();

        $student = new Student($fn, $ln, $email);

        $student->joinGroup($group1);
        $this->assertCount(1, $student->getGroups());

        $student->joinGroup($group2);
        $this->assertCount(2, $student->getGroups());
        $this->assertCount(2, $student->getActiveGroups());
    }

    /**
     * @test
     * @group group student
     */
    public function should_end_active_group_for_student()
    {
        $group1 = new Group($this->faker->word());
        $group2 = new Group($this->faker->word());

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();

        $student = new Student($fn, $ln, $email);

        $student->joinGroup($group1);
        $this->assertCount(1, $student->getGroups());

        $student->joinGroup($group2, null, Carbon::now());
        $this->assertCount(2, $student->getGroups());
        $this->assertCount(1, $student->getActiveGroups());
    }

    /**
     * @test
     * @group group
     * @group student
     */
    public function should_leave_active_group_for_student()
    {
        $group1 = new Group($this->faker->word());
        $group2 = new Group($this->faker->word());

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();

        $student = new Student($fn, $ln, $email);

        $student->joinGroup($group1)
            ->joinGroup($group2, null, Carbon::now()); //once was in group2
        $this->assertCount(1, $student->getActiveGroups());

        $student->joinGroup($group2); //again in group2
        $this->assertCount(3, $student->getGroups());
        $this->assertCount(2, $student->getActiveGroups());

        $student->leaveGroup($group2); //leave group2 again
        $this->assertCount(3, $student->getGroups());
        $this->assertCount(1, $student->getActiveGroups());
    }

    /**
     * @test
     * @group student
     * @group group
     */
    public function should_been_active_at()
    {
        $group1 = new Group($this->faker->word());
        $group2 = new Group($this->faker->word());

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();

        $user = new Student($fn, $ln, $email);

        $nearInfinite = new DateTime('9999-01-01');
        $lowerBound = new DateTime('2014-01-01');
        $upperBound = new DateTime('2016-01-01');

        $user->joinGroup($group1, $lowerBound)
            ->joinGroup($group2, $lowerBound, $upperBound);


        $this->assertTrue($user->wasActiveInGroupAt($group1, $nearInfinite));
        $this->assertTrue($user->wasActiveInGroupAt($group1, $lowerBound));
        $this->assertTrue($user->wasActiveInGroupAt($group1, $upperBound));

        $this->assertTrue($user->wasActiveInGroupAt($group2, $lowerBound));
        $this->assertTrue($user->wasActiveInGroupAt($group2, $upperBound));
        $this->assertFalse($user->wasActiveInGroupAt($group2, $nearInfinite));
    }

    /**
     * @test
     * @group student
     * @group group
     */
    public function should_been_active_between()
    {
        $group1 = new Group($this->faker->word());
        $group2 = new Group($this->faker->word());

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();

        $student = new Student($fn, $ln, $email);

        $nearInfinite = new DateTime('9999-01-01');
        $lowerBound = new DateTime('2014-01-01');
        $upperBound = new DateTime('2016-01-01');

        $student->joinGroup($group1, $lowerBound)
            ->joinGroup($group2, $lowerBound, $upperBound);


        $this->assertTrue($student->wasActiveInGroupBetween($group1, new DateRange($lowerBound, $upperBound)));
        $this->assertTrue($student->wasActiveInGroupBetween($group1, new DateRange($lowerBound, $nearInfinite)));

        $this->assertTrue($student->wasActiveInGroupBetween($group2, new DateRange($lowerBound, $upperBound)));
        $this->assertFalse($student->wasActiveInGroupBetween($group2, new DateRange($lowerBound, $nearInfinite)));
    }
}
