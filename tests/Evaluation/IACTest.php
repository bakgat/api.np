<?php
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\Goal;
use App\Domain\Model\Evaluation\IAC;
use App\Domain\Model\Evaluation\IACGoal;
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Time\DateRange;
use App\Domain\NtUid;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 5/09/16
 * Time: 22:30
 */
class IACTest extends TestCase
{
    /**
     * @test
     * @group IAC
     */
    public function should_create_new()
    {
        $now = new DateTime;
        $student = $this->makeStudent();
        $iac = new IAC($student, ['start' => $now]);

        $this->assertInstanceOf(IAC::class, $iac);
        $this->assertInstanceOf(Student::class, $iac->getStudent());
        $this->assertInstanceOf(NtUid::class, $iac->getId());
        $this->assertEquals($now, $iac->isActiveSince());
        $this->assertEquals(DateRange::FUTURE, $iac->isActiveUntil()->format('Y-m-d'));
        $this->assertTrue($iac->isActive());
    }

    /**
     * @test
     * @group IAC
     */
    public function should_add_goal()
    {
        $now = new DateTime;
        $student = $this->makeStudent();
        $iac = new IAC($student, ['start' => $now]);

        $text = $this->faker->text;
        $branch = $this->makeBranch();
        $goal = new Goal($branch, $text);

        $iacGoal = $iac->addGoal($goal, $now);

        $this->assertInstanceOf(IACGoal::class, $iacGoal);
        $this->assertCount(1, $iac->allIACGoals());
        $this->assertEquals($goal, $iacGoal->getGoal());
        $this->assertEquals($now, $iacGoal->getDate());
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

    /**
     * @return Branch
     */
    private function makeBranch()
    {
        $branch = new Branch($this->faker->word);
        return $branch;
    }


}
