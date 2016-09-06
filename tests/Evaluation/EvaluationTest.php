<?php
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\BranchForGroup;
use App\Domain\Model\Evaluation\Evaluation;
use App\Domain\Model\Evaluation\EvaluationType;
use App\Domain\Model\Identity\Group;
use App\Domain\Uuid;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 5/09/16
 * Time: 23:01
 */
class EvaluationTest extends TestCase
{
    /**
     * @test
     * @group evaluation
     */
    public function should_create_new_point_evaluation()
    {
        $now = new DateTime;
        $dr = ['start' => $now];

        $branch = $this->makeBranch();
        $group = $this->makeGroup();
        $evType = new EvaluationType(EvaluationType::POINT);
        $maxForBranch = $this->faker->biasedNumberBetween(20, 50);
        $branchForGroup = new BranchForGroup($branch, $group, $dr, $evType, $maxForBranch);

        $title = $this->faker->word;
        $max = $this->faker->biasedNumberBetween(10, 100);

        $evaluation = new Evaluation($branchForGroup, $title, $now, $max);

        $this->assertInstanceOf(Evaluation::class, $evaluation);
        $this->assertInstanceOf(Uuid::class, $evaluation->getId());

        $this->assertInstanceOf(Branch::class, $evaluation->getBranch());
        $this->assertEquals($branch, $evaluation->getBranch());

        $this->assertInstanceOf(EvaluationType::class, $evaluation->getEvaluationType());
        $this->assertEquals($evType, $evaluation->getEvaluationType());

        $this->assertEquals($title, $evaluation->getTitle());
        $this->assertEquals($max, $evaluation->getMax());

        $this->assertEquals($now, $evaluation->getDate());
    }

    /**
     * @test
     * @group evaluation
     */
    public function should_create_new_comprehensive_evaluation()
    {
        $now = new DateTime;
        $dr = ['start' => $now];

        $branch = $this->makeBranch();
        $group = $this->makeGroup();
        $evType = new EvaluationType(EvaluationType::COMPREHENSIVE);
        $branchForGroup = new BranchForGroup($branch, $group, $dr, $evType);

        $title = $this->faker->word;
        $evaluation = new Evaluation($branchForGroup, $title);

        $this->assertInstanceOf(Evaluation::class, $evaluation);
        $this->assertInstanceOf(Uuid::class, $evaluation->getId());

        $this->assertInstanceOf(Branch::class, $evaluation->getBranch());
        $this->assertEquals($branch, $evaluation->getBranch());

        $this->assertInstanceOf(EvaluationType::class, $evaluation->getEvaluationType());
        $this->assertEquals($evType, $evaluation->getEvaluationType());

        $this->assertEquals($title, $evaluation->getTitle());
        $this->assertEquals($now->format('Y-m-d'), $evaluation->getDate()->format('Y-m-d'));
    }

    /* ***************************************************
     * PRIVATE METHODS
     * **************************************************/
    /*
    * @return Branch
    */
    private function makeBranch()
    {
        $branch = new Branch($this->faker->word);
        return $branch;
    }

    /**
     * @return Group
     */
    private function makeGroup()
    {
        $group = new Group($this->faker->word);
        return $group;
    }
}
