<?php
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Student;

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

        $user->joinGroup($group2, null, new \DateTime());
        $this->assertCount(2, $user->groups());
        $this->assertCount(1, $user->activeGroups());
    }

    /**
     * @test
     * @group group student
     */
    public function should_leave_active_group_for_user() {
        $group1 = new Group($this->faker->word());
        $group2 = new Group($this->faker->word());

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();

        $user = new Student($fn, $ln, $email);

        $user->joinGroup($group1)
            ->joinGroup($group2, null, new DateTime()); //once was in group2
        $this->assertCount(1, $user->activeGroups());

        $user->joinGroup($group2); //again in group2
        $this->assertCount(3, $user->groups());
        $this->assertCount(2, $user->activeGroups());

        $user->leaveGroup($group2); //leave group2 again
        $this->assertCount(3, $user->groups());
        $this->assertCount(1, $user->activeGroups());
    }
}
