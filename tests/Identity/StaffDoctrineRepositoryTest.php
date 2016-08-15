<?php
use App\Domain\Model\Identity\StaffRepository;
use App\Repositories\Identity\StaffDoctrineRepository;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 15/08/16
 * Time: 14:43
 */
class StaffDoctrineRepositoryTest extends DoctrineTestCase
{
    /** @var  StaffRepository */
    protected $staffRepo;

    public function setUp()
    {
        parent::setUp();

        $this->staffRepo = new  StaffDoctrineRepository($this->em);
    }

    /**
     * @test
     * @group staff
     * @group staffrepo
     * @group all
     */
    public function should_return_41_staff_members()
    {
        $staff = $this->staffRepo->all();

        $this->assertCount(41, $staff);
    }
}
