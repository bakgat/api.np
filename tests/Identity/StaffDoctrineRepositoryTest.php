<?php
use App\Domain\Model\Identity\Staff;
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

    protected $emailKarl = 'karl.vaniseghem@klimtoren.be';

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

    /**
     * @test
     * @group staff
     * @group staffrepo
     * @group find
     */
    public function should_find_by_email_address()
    {
        $karl = $this->staffRepo->findByEmail($this->emailKarl);

        $this->assertInstanceOf(Staff::class, $karl);
        $this->assertEquals($karl->getEmail(), $this->emailKarl);
    }

    /**
     * @test
     * @group staff
     * @group staffrepo
     * @group find
     */
    public function should_return_null_when_not_found_through_email()
    {
        // make fake unique email address
        $fakeMail = \Webpatser\Uuid\Uuid::generate(4) . '@test.com';

        $nullUser  = $this->staffRepo->findByEmail($fakeMail);

        $this->assertNull($nullUser);
    }

    /**
     * @test
     * @group staff
     * @group staffrepo
     * @group staffrole
     */
    public function should_get_2_roles_for_karl()
    {
        /** @var Staff $karl */
        $karl = $this->staffRepo->findByEmail($this->emailKarl);
        $roles = $karl->getRoles();
        $this->assertCount(2, $roles);
    }
}
