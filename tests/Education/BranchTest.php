<?php
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\Major;
use App\Domain\Model\Evaluation\EvaluationType;
use App\Domain\Model\Identity\Group;
use Webpatser\Uuid\Uuid;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 14/07/16
 * Time: 22:01
 */
class BranchTest extends TestCase
{

    /**
     * @test
     * @group branch
     * @group education
     */
    public function should_create_new()
    {
        $major_name = $this->faker->word();
        $major = new Major($major_name);

        $branch_name = $this->faker->word();
        $branch = new Branch($branch_name);

        $this->assertInstanceOf(Uuid::class, $major->getId());
        $this->assertEquals($major_name, $major->getName());

        $this->assertEquals($major, $branch->getMajor());

        $this->assertInstanceOf(Uuid::class, $branch->getId());
        $this->assertEquals($branch_name, $branch->getName());
    }

    /**
     * @test
     * @group branch
     * @group education
     */
    public function should_update_existing_branch()
    {

        $branch_name = $this->faker->unique()->word();
        $branch = new Branch($branch_name);


        $new_branch_name = $this->faker->unique()->word();
        $branch->changeName($new_branch_name);

        $this->assertNotEquals($branch_name, $branch->getName());
        $this->assertEquals($new_branch_name, $branch->getName());
    }

    /**
     * @test
     * @group branch
     * @group education
     * @group group
     */
    public function should_join_groups()
    {
        $group1 = new Group($this->faker->word());
        $group2 = new Group($this->faker->word());
        $group3 = new Group($this->faker->word());

        $pEvaluation = new EvaluationType(EvaluationType::POINT);
        $cEvaluation = new EvaluationType(EvaluationType::COMPREHENSIVE);
        $fEvaluation = new EvaluationType(EvaluationType::FEEDBACK);

        $max = 20;

        $branch = new Branch($this->faker->word());
        $branch->joinGroup($group1, $pEvaluation, $max);
        $this->assertCount(1, $branch->getGroups());

        $branch->joinGroup($group2, $cEvaluation);
        $branch->joinGroup($group3, $fEvaluation);

        $this->assertCount(3, $branch->getGroups());
        $this->assertCount(3, $branch->getActiveGroups());

        $this->assertCount(1, $branch->getGroups($pEvaluation));
        $this->assertCount(1, $branch->getGroups($cEvaluation));
        $this->assertCount(1, $branch->getGroups($fEvaluation));

        $this->assertCount(1, $branch->getActiveGroups($pEvaluation));
        $this->assertCount(1, $branch->getActiveGroups($cEvaluation));
        $this->assertCount(1, $branch->getActiveGroups($fEvaluation));
    }

    /**
     * @test
     * @group branch
     * @group education
     * @group group
     */
    public function should_register_inactive_group_for_branch()
    {
        $group1 = new Group($this->faker->word());
        $group2 = new Group($this->faker->word());

        $pEvaluation = new EvaluationType(EvaluationType::POINT);
        $cEvaluation = new EvaluationType(EvaluationType::COMPREHENSIVE);
        $max = 20;

        $branch = new Branch($this->faker->word());

        $branch->joinGroup($group1, $pEvaluation, $max);
        $this->assertCount(1, $branch->getGroups());

        $now = new DateTime;
        $branch->joinGroup($group2, $cEvaluation, null, null, $now->modify('-1 day'));
        $this->assertCount(2, $branch->getGroups());
        $this->assertCount(1, $branch->getActiveGroups());

        $this->assertCount(0, $branch->getActiveGroups($cEvaluation));
    }

    /**
     * @test
     * @group branch
     * @group education
     * @group group
     */
    public function should_leave_active_group_for_branch()
    {
        $group1 = new Group($this->faker->word());
        $group2 = new Group($this->faker->word());

        $pEvaluation = new EvaluationType(EvaluationType::POINT);
        $cEvaluation = new EvaluationType(EvaluationType::COMPREHENSIVE);
        $max = 20;

        $branch = new Branch($this->faker->word());

        $branch->joinGroup($group1, $pEvaluation, $max)
            ->joinGroup($group2, $cEvaluation);

        $this->assertCount(2, $branch->getActiveGroups());

        $branch->leaveGroup($group2);
        $this->assertCount(2, $branch->getGroups());
        $this->assertCount(1, $branch->getActiveGroups());
        $this->assertCount(0, $branch->getActiveGroups($cEvaluation));
    }
}
