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
    /** @var Faker\Generator */
    private $faker;

    public function setUp()
    {
        parent::setUp();
        $this->faker = Faker\Factory::create('nl_BE');
    }

    public function tearDown()
    {
        parent::tearDown();
    }


    /**
     * @test
     * @group group student
     */
    public function should_add_user_to_groups()
    {
        $group1 = new Group($this->faker->word());
        $group2 = new Group($this->faker->word());

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();

        $user = new Student($fn, $ln, $email);

        $user->joinGroup($group1);
        $this->assertCount(1, $user->groups());

        $user->joinGroup($group2);
        $this->assertCount(2, $user->groups());
        $this->assertCount(2, $user->activeGroups());
    }

    /**
     * @test
     * @group group student
     */
    public function should_end_active_group_for_user()
    {
        $group1 = new Group($this->faker->word());
        $group2 = new Group($this->faker->word());

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();

        $user = new Student($fn, $ln, $email);

        $user->joinGroup($group1);
        $this->assertCount(1, $user->groups());

        $user->joinGroup($group2, null, Carbon::now());
        $this->assertCount(2, $user->groups());
        $this->assertCount(1, $user->activeGroups());
    }

    /**
     * @test
     * @group group
     * @group student
     */
    public function should_leave_active_group_for_user()
    {
        $group1 = new Group($this->faker->word());
        $group2 = new Group($this->faker->word());

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();

        $user = new Student($fn, $ln, $email);

        $user->joinGroup($group1)
            ->joinGroup($group2, null, Carbon::now()); //once was in group2
        $this->assertCount(1, $user->activeGroups());

        $user->joinGroup($group2); //again in group2
        $this->assertCount(3, $user->groups());
        $this->assertCount(2, $user->activeGroups());

        $user->leaveGroup($group2); //leave group2 again
        $this->assertCount(3, $user->groups());
        $this->assertCount(1, $user->activeGroups());
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

        $user = new Student($fn, $ln, $email);

        $nearInfinite = new DateTime('9999-01-01');
        $lowerBound = new DateTime('2014-01-01');
        $upperBound = new DateTime('2016-01-01');

        $user->joinGroup($group1, $lowerBound)
            ->joinGroup($group2, $lowerBound, $upperBound);


        $this->assertTrue($user->wasActiveInGroupBetween($group1, new DateRange($lowerBound, $upperBound)));
        $this->assertTrue($user->wasActiveInGroupBetween($group1, new DateRange($lowerBound, $nearInfinite)));

        $this->assertTrue($user->wasActiveInGroupBetween($group2, new DateRange($lowerBound, $upperBound)));
        $this->assertFalse($user->wasActiveInGroupBetween($group2, new DateRange($lowerBound, $nearInfinite)));
    }
}
