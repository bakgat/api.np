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
 * Date: 3/08/16
 * Time: 09:21
 */
class PointResultTest extends TestCase
{
    /**
     * @test
     * @group evaluation
     * @group result
     *
     */
    public function should_create_new_evaluation()
    {
        $branch = new Branch($this->faker->word());
        $group = new Group($this->faker->word());
        $evType = new EvaluationType(EvaluationType::POINT);
        $max = 20;

        $branchForGroup = new BranchForGroup($branch, $group, ['start' => new DateTime], $evType, $max);

        $title = $this->faker->words(3);

        $evaluation = new Evaluation($branchForGroup, $title, new DateTime, 100);


        foreach (range(1, 10) as $index) {
            $fn = $this->faker->firstName();
            $ln = $this->faker->lastName();
            $email = $this->faker->email();
            $gender = new Gender(Gender::MALE);

            $student = new Student($fn, $ln, $email, $gender);
            $result = new PointResult($student, $index * 10);
            $evaluation->addResult($result);
        }


        $this->assertEquals(55, $evaluation->getAverage());
        $this->assertEquals(55, $evaluation->getMedian());
        $this->assertEquals(100, $evaluation->getMax());
        $this->assertEquals($evType, $evaluation->getEvaluationType());
        $this->assertEquals($branch, $evaluation->getBranch());
        $this->assertEquals($group, $evaluation->getGroup());

        //Add an test for median with odd number of evaluations
        foreach (range(1, 9) as $index) {
            $fn = $this->faker->firstName();
            $ln = $this->faker->lastName();
            $email = $this->faker->email();
            $gender = new Gender(Gender::MALE);

            $student = new Student($fn, $ln, $email, $gender);
            $result = new PointResult($student, $index * 10);

            $this->assertInstanceOf(NtUid::class, $result->getId());
            $evaluation->addResult($result);

            $this->assertInstanceOf(Evaluation::class, $result->getEvaluation());
            $this->assertEquals($evaluation, $result->getEvaluation());
        }


        $this->assertEquals(52.631578947368418, $evaluation->getAverage());
        $this->assertEquals(50, $evaluation->getMedian());
        $this->assertEquals(100, $evaluation->getMax());
        $this->assertEquals($evType, $evaluation->getEvaluationType());
        $this->assertEquals($branch, $evaluation->getBranch());
        $this->assertEquals($group, $evaluation->getGroup());
    }

}
