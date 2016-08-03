<?php
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Evaluation\Evaluation;
use App\Domain\Model\Evaluation\EvaluationType;
use App\Domain\Model\Evaluation\PointResult;
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Student;

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
    public function should_create_new()
    {
        $branch = new Branch($this->faker->word());
        $evType = new EvaluationType(EvaluationType::POINT);
        $title = $this->faker->words(3);

        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $email = $this->faker->email();
        $gender = new Gender(Gender::MALE);

        $evaluation = new Evaluation($branch, $evType, $title, new DateTime, 30);
        $student = new Student($fn, $ln, $email, $gender);

        $result = new PointResult($student, 20);
        $evaluation->addResult($result);
        $result = new PointResult($student, 30);
        $evaluation->addResult($result);

        $this->assertEquals(25, $evaluation->getAverage());
        //$this->assertEquals(30, $evaluation->getMax());
        $this->assertEquals(25, $evaluation->getMedian());
    }
}
