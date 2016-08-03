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

        $evaluation = new Evaluation($branch, $evType, $title, new DateTime, 100);


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
    }
}
