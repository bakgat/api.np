<?php
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\Redicodi;
use App\Domain\Model\Evaluation\RedicodiForStudent;
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Student;
use App\Domain\NtUid;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 1/08/16
 * Time: 16:09
 */
class RedicodiForStudentTest extends TestCase
{
    /**
     * @test
     * @group evaluation
     * @group Student
     * @group Redicodi
     * @group Branch
     * @group Group
     */
    public function should_create_new()
    {
        $student = $this->makeStudent();
        $branch = $this->makeBranch();

        $redicodi = new Redicodi(Redicodi::BASIC);

        $dateRange = ['start' => new DateTime];
        $content = $this->faker->text(5);

        $rfs = new RedicodiForStudent($student, $redicodi, $branch, $content, $dateRange);

        $this->assertInstanceOf(RedicodiForStudent::class, $rfs);
        $this->assertInstanceOf(NtUid::class, $rfs->getId());
        $this->assertInstanceOf(Student::class, $rfs->getStudent());
        $this->assertInstanceOf(Branch::class, $rfs->getBranch());
        $this->assertInstanceOf(Redicodi::class, $rfs->getRedicodi());

        $this->assertEquals($content, $rfs->getContent());
        $this->assertTrue($rfs->isActive());

    }

    /**
     * @test
     * @group evaluation
     * @group Student
     * @group Redicodi
     * @group Branch
     * @group Group
     */
    public function should_stop_redicodi()
    {
        $student = $this->makeStudent();
        $branch = $this->makeBranch();

        $redicodi = new Redicodi(Redicodi::BASIC);

        $now = new DateTime;

        $farpast = clone $now->modify('-2 year');
        $dateRange = ['start' => $farpast];
        $content = $this->faker->text(5);

        $rfs = new RedicodiForStudent($student, $redicodi, $branch, $content, $dateRange);
        $this->assertTrue($rfs->isActive());


        $past = clone $now->modify('-1 year');

        $rfs->stopRedicodi($past);
        $this->assertFalse($rfs->isActive());
    }

    private function makeStudent()
    {
        $student_fn = $this->faker->firstName();
        $student_ln = $this->faker->lastName();
        $student_email = $this->faker->email();
        $student = new Student($student_fn, $student_ln, $student_email, new Gender('M'));
        return $student;
    }

    /**
     * @return Branch
     */
    private function makeBranch()
    {
        $branch_name = $this->faker->unique()->word();
        $branch = new Branch($branch_name);
        return $branch;
    }
}
