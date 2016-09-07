<?php
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\BranchForGroup;
use App\Domain\Model\Evaluation\Evaluation;
use App\Domain\Model\Evaluation\EvaluationType;
use App\Domain\Model\Evaluation\PointResult;
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Student;
use App\Domain\NtUid;

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
        $this->assertInstanceOf(NtUid::class, $evaluation->getId());

        $this->assertInstanceOf(Branch::class, $evaluation->getBranch());
        $this->assertEquals($branch, $evaluation->getBranch());

        $this->assertInstanceOf(EvaluationType::class, $evaluation->getEvaluationType());
        $this->assertEquals($evType, $evaluation->getEvaluationType());

        $this->assertEquals($title, $evaluation->getTitle());
        $this->assertEquals($max, $evaluation->getMax());
        $this->assertTrue($evaluation->isPermanent());

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
        $this->assertInstanceOf(NtUid::class, $evaluation->getId());

        $this->assertInstanceOf(Branch::class, $evaluation->getBranch());
        $this->assertEquals($branch, $evaluation->getBranch());

        $this->assertInstanceOf(EvaluationType::class, $evaluation->getEvaluationType());
        $this->assertEquals($evType, $evaluation->getEvaluationType());

        $this->assertEquals($title, $evaluation->getTitle());
        $this->assertEquals($now->format('Y-m-d'), $evaluation->getDate()->format('Y-m-d'));

        $this->assertTrue($evaluation->isPermanent());
    }

    /**
     * @test
     * @group evaluation
     */
    public function should_update()
    {
        $now = new DateTime;
        $dr = ['start' => $now];

        $branch = $this->makeBranch();
        $group = $this->makeGroup();
        $evType = new EvaluationType(EvaluationType::COMPREHENSIVE);
        $branchForGroup = new BranchForGroup($branch, $group, $dr, $evType);

        $title = $this->faker->unique()->word;
        $evaluation = new Evaluation($branchForGroup, $title);


        $max = 20;
        $newBranch = $this->makeBranch();
        $newGroup = $this->makeGroup();
        $newEvType = new EvaluationType(EvaluationType::POINT);
        $newBranchForGroup = new BranchForGroup($newBranch, $newGroup, $dr, $newEvType, $max);

        $newTitle = $this->faker->unique()->word;

        $past = clone $now->modify('-1 year');
        $evaluation->update($newTitle, $newBranchForGroup, $past, $max);

        $this->assertEquals($newBranch, $evaluation->getBranch());
        $this->assertEquals($newEvType, $evaluation->getEvaluationType());
        $this->assertEquals($newTitle, $evaluation->getTitle());
        $this->assertEquals($past->format('Y-m-d'), $evaluation->getDate()->format('Y-m-d'));
        $this->assertEquals($max, $evaluation->getMax());
        $this->assertTrue($evaluation->isPermanent());
    }


    /**
     * @test
     * @group evaluation
     */
    public function should_update_result()
    {
        $now = new DateTime;
        $dr = ['start' => $now];

        $branch = $this->makeBranch();
        $group = $this->makeGroup();
        $evType = new EvaluationType(EvaluationType::COMPREHENSIVE);
        $branchForGroup = new BranchForGroup($branch, $group, $dr, $evType);

        $title = $this->faker->word;
        $evaluation = new Evaluation($branchForGroup, $title);

        foreach (range(1, 10) as $item) {
            $evaluation->addResult($this->makePointResult());
        }
        /** @var PointResult $result */
        $result = $evaluation->getResults()[0];
        $student = $result->getStudent();

        $this->assertEquals(20, $result->getScore());


        /* UPDATE THE RESULT */
        $evaluation->updateResult($student, 30, []);
        $this->assertEquals(30, $result->getScore());
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

    /**
     * @return Student
     */
    private function makeStudent()
    {
        $fn = $this->faker->firstName;
        $ln = $this->faker->lastName;
        $schoolid = $this->faker->bankAccountNumber;
        $gender = $this->faker->randomElement(Gender::values());
        $student = new Student($fn, $ln, $schoolid, $gender);
        return $student;
    }

    private function makePointResult()
    {
        $pr = new PointResult($this->makeStudent(), 20);
        return $pr;
    }
}
