<?php
use App\Domain\Model\Identity\Exceptions\StaffNotFoundException;
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Staff;
use App\Domain\Model\Identity\StaffRepository;
use App\Domain\Uuid;
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
        $fakeMail = Uuid::generate(4) . '@test.com';

        $nullUser = $this->staffRepo->findByEmail($fakeMail);

        $this->assertNull($nullUser);
    }

    /**
     * @test
     * @group staff
     * @group staffrepo
     * @group find
     */
    public function should_find_by_its_id()
    {
        $staff = $this->staffRepo->all();

        $id = $staff[0]->getId();

        $this->em->clear();

        $staff = $this->staffRepo->find($id);

        $this->assertInstanceOf(Staff::class, $staff);
        $this->assertEquals($staff->getId(), $id);
    }

    /**
     * @test
     * @group staff
     * @group staffrepo
     * @group find
     */
    public function should_return_null_when_no_staff_found()
    {
        $fakeId = Uuid::generate(4);
        $staff = $this->staffRepo->find($fakeId);
        $this->assertNull($staff);
    }

    /**
     * @test
     * @group staff
     * @group staffrepo
     * @group staffrole
     */
    public function should_get_1_roles_for_staff()
    {
        /** @var Staff $staff */
        $staff = $this->staffRepo->all()[0];
        $roles = $staff->getRoles();
        $this->assertCount(1, $roles);
    }

    /**
     * @test
     * @group staff
     * @group staffrepo
     * @group get
     */
    public function should_get_staff_by_its_id()
    {
        $staff = $this->staffRepo->all();

        $id = $staff[0]->getId();

        $this->em->clear();

        $staff = $this->staffRepo->get($id);

        $this->assertInstanceOf(Staff::class, $staff);
        $this->assertEquals($staff->getId(), $id);
    }

    /**
     * @test
     * @group staff
     * @group staffrepo
     * @group get
     */
    public function should_throw_exception_when_get_staff_fails()
    {
        $this->setExpectedException(StaffNotFoundException::class);
        $fakeId = Uuid::generate(4);
        $staff = $this->staffRepo->get($fakeId);
    }

    /**
     * @test
     * @group staff
     * @group staffrepo
     * @group insert
     */
    public function should_insert_new_staff()
    {
        $staff = $this->makeStaff();

        $id = $this->staffRepo->insert($staff);
        $this->em->clear();

        $dbStaff = $this->staffRepo->get($id);

        $this->assertInstanceOf(Staff::class, $dbStaff);
        $this->assertEquals($staff->getId(), $dbStaff->getId());
        $this->assertEquals($staff->getDisplayName(), $dbStaff->getDisplayName());
        $this->assertEquals($staff->getEmail(), $dbStaff->getEmail());
        $this->assertEquals($staff->getGender(), $dbStaff->getGender());
        $this->assertEquals($staff->getBirthday()->format('Y-m-d'), $dbStaff->getBirthday()->format('Y-m-d'));
    }

    /**
     * @test
     * @group staff
     * @group staffrepo
     * @group update
     */
    public function should_update_existing_staff()
    {
        $staff = $this->makeStaff();
        $id = $this->staffRepo->insert($staff);

        $this->em->clear();

        $dbStaff = $this->staffRepo->get($id);

        $dbStaff->updateProfile('Karl', 'Van Iseghem', 'karl.vaniseghem@klimtoren.be', new Gender('M'), new DateTime('1979-11-30'));
        $count = $this->staffRepo->update($dbStaff);

        $this->em->clear();

        $savedStaff = $this->staffRepo->get($id);

        $this->assertInstanceOf(Staff::class, $savedStaff);
        $this->assertEquals(1, $count);

        $this->assertNotEquals($staff->getDisplayName(), $savedStaff->getDisplayName());
        $this->assertNotEquals($staff->getBirthday(), $savedStaff->getBirthday());
        $this->assertNotEquals($staff->getEmail(), $savedStaff->getEmail());
        $this->assertNotEquals($staff->getGender(), $savedStaff->getGender());

        $this->assertEquals($staff->getId(), $savedStaff->getId());
        $this->assertEquals($dbStaff->getId(), $savedStaff->getId());
        $this->assertEquals($dbStaff->getDisplayName(), $savedStaff->getDisplayName());
        $this->assertEquals($dbStaff->getBirthday(), $savedStaff->getBirthday());
        $this->assertEquals($dbStaff->getEmail(), $savedStaff->getEmail());
        $this->assertEquals($dbStaff->getGender(), $savedStaff->getGender());
    }

    /**
     * @test
     * @group staff
     * @group staffrepo
     * @group delete
     */
    public function should_delete_existing_staff() {
        $staff = $this->makeStaff();
        $id = $this->staffRepo->insert($staff);

        $this->em->clear();

        $savedStaff = $this->staffRepo->get($id);

        $count = $this->staffRepo->delete($id);

        $this->em->clear();

        $removedStaff = $this->staffRepo->find($id);

        $this->assertEquals($id, $savedStaff->getId());
        $this->assertEquals(1, $count);
        $this->assertNull($removedStaff);
    }

    /**
     * @return Staff
     */
    private function makeStaff()
    {
        $fn = $this->faker->firstName;
        $ln = $this->faker->lastName;
        $email = $this->faker->email;
        $gender = $this->faker->randomElement(Gender::values());
        $birthday = $this->faker->dateTime;
        $staff = new Staff($fn, $ln, $email, $gender, $birthday);
        return $staff;
    }
}
