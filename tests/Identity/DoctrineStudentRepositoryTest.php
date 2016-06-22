<?php
use App\Domain\Model\Identity\StudentRepository;
use App\Repositories\Identity\DoctrineStudentRepository;


/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 22/06/16
 * Time: 16:27
 */
class DoctrineStudentRepositoryTest extends DoctrineTestCase
{
    /** @var StudentRepository */
    protected $studentRepo;

    public function setUp()
    {
        parent::setUp();

        $this->studentRepo = new DoctrineStudentRepository($this->em);
    }

    /**
     * @test
     * @group student
     * @group studentrepo
     */
    public function should_return_440_students()
    {
        $students = $this->studentRepo->all();

        $this->assertCount(440, $students);
    }

    /**
     * @test
     * @group student
     * @group studentrepo
     */
    public function should_have_1_active_group_per_user()
    {
        $students = $this->studentRepo->all();
        foreach ($students as $student) {
            $this->assertCount(1, $student->activeGroups());
        }
    }

}
