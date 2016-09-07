<?php
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\Goal;
use App\Domain\Model\Evaluation\IAC;
use App\Domain\Model\Evaluation\IACGoal;
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Student;
use App\Domain\NtUid;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 5/09/16
 * Time: 22:44
 */
class IACGoalTest extends TestCase
{
    /**
     * @test
     * @group IACGoal
     */
    public function should_create_new()
    {
        $now = new DateTime;
        $student = $this->makeStudent();
        $branch = $this->makeBranch();
        $text = $this->faker->text();

        $iac = new IAC($student, ['start' => $now]);
        $goal = new Goal($branch, $text);
        $iacGoal = new IACGoal($iac, $goal, $now->modify('+1 day'));

        $this->assertInstanceOf(IACGoal::class, $iacGoal);
        $this->assertInstanceOf(NtUid::class, $iacGoal->getId());

        $this->assertInstanceOf(IAC::class, $iacGoal->getIac());
        $this->assertEquals($iac, $iacGoal->getIac());

        $this->assertInstanceOf(Goal::class, $iacGoal->getGoal());
        $this->assertEquals($goal, $iacGoal->getGoal());

        $this->assertEquals($now, $iacGoal->getDate());

        $now = new DateTime;
        $iacGoal = new IACGoal($iac, $goal);
        //TEST WITHOUT DATE GIVEN
        $this->assertEquals($now->format('Y-m-d'), $iacGoal->getDate()->format('Y-m-d'));

        $this->assertNull($iacGoal->isAchieved());
        $this->assertNull($iacGoal->isPractice());
    }

    /**
     * @test
     * @group IACGoal
     */
    public function should_set_achieved_practice()
    {
        $now = new DateTime;
        $student = $this->makeStudent();
        $branch = $this->makeBranch();
        $text = $this->faker->text();

        $iac = new IAC($student, ['start' => $now]);
        $goal = new Goal($branch, $text);
        $iacGoal = new IACGoal($iac, $goal, $now);

        $comment = $this->faker->unique(true)->text;

        //FIRST ACHIEVED
        sleep(1); //force datetime adjustment
        $iacGoal->setAchieved($comment);
        $this->assertTrue($iacGoal->isAchieved());
        $this->assertFalse($iacGoal->isPractice());
        $this->assertEquals($comment, $iacGoal->getComment());
        $this->assertGreaterThan($now, $iacGoal->getDate());

        $achievedDateTime = clone $iacGoal->getDate();
        $newComment = $this->faker->unique()->text;

        //THEN PRACICE
        sleep(1); //force datetime adjustment
        $iacGoal->setPractice($newComment);
        $this->assertFalse($iacGoal->isAchieved());
        $this->assertTrue($iacGoal->isPractice());
        $this->assertEquals($newComment, $iacGoal->getComment());
        $this->assertGreaterThan($achievedDateTime, $iacGoal->getDate());
    }

    /* ***************************************************
     * PRIVATE METHODS
     * **************************************************/
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

    /*
    * @return Branch
    */
    private function makeBranch()
    {
        $branch = new Branch($this->faker->word);
        return $branch;
    }
}
