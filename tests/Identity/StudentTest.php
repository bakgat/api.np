<?php
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Student;
use Webpatser\Uuid\Uuid;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 20/06/16
 * Time: 21:38
 */
class StudentTest extends TestCase
{
    /**
     * @test
     * @group student
     */
    public function should_create_new()
    {
        $fn = 'Karl';
        $ln = 'Van Iseghem';
        $email = 'karl.vaniseghem@klimtoren.be';
        $gender = new Gender($this->faker->randomElement(['F', 'M']));

        $student = new Student($fn, $ln, $email, $gender);

        $this->assertInstanceOf(Uuid::class, $student->getId());
        $this->assertEquals($fn . ' ' . $ln, $student->getDisplayName());
        $this->assertCount(5, explode('-', $student->getId()));
    }
}
