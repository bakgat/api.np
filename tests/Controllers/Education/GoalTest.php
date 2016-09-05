<?php
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\Goal;
use App\Domain\Uuid;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 5/09/16
 * Time: 16:16
 */
class GoalTest extends TestCase
{
    /**
     * @test
     * @group goal
     */
    public function should_create_new()
    {
        $branch = new Branch($this->faker->word());
        $text = $this->faker->text();
        $goal = new Goal($branch, $text);

        $this->assertInstanceOf(Goal::class, $goal);
        $this->assertInstanceOf(Uuid::class, $goal->getId());
        $this->assertEquals($branch , $goal->getBranch());
        $this->assertEquals($text, $goal->getText());
    }

    /**
     * @test
     * @group goal
     */
    public function should_switch_branch()
    {
        $goal = $this->makeGoal();

        $oldBranch = $goal->getBranch();
        $newBranch = new Branch($this->faker->unique()->word);

        $goal->switchBranch($newBranch);

        $this->assertNotEquals($oldBranch, $newBranch);
        $this->assertEquals($newBranch, $goal->getBranch());

    }

    /**
     * @test
     * @group goal
     */
    public function should_update_text()
    {
        $goal = $this->makeGoal();

        $oldText = $goal->getText();
        $newText = $this->faker->unique()->text;

        $goal->updateText($newText);

        $this->assertNotEquals($oldText, $newText);
        $this->assertEquals($newText, $goal->getText());
    }

    private function makeGoal()
    {
        $branch = new Branch($this->faker->unique(true)->word);
        $goal = new Goal($branch, $this->faker->unique(true)->text);
        return $goal;
    }
}
