<?php
use App\Domain\Model\Identity\Exceptions\RoleNotFoundException;
use App\Domain\Model\Identity\Role;
use App\Domain\Model\Identity\RoleRepository;
use App\Domain\Model\Identity\Staff;
use App\Domain\Model\Identity\StaffRepository;
use App\Domain\Model\Identity\StaffRole;
use App\Domain\NtUid;
use App\Repositories\Identity\RoleDoctrineRepository;
use App\Repositories\Identity\StaffDoctrineRepository;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 6/09/16
 * Time: 16:44
 */
class RoleDoctrineRepositoryTest extends DoctrineTestCase
{
    /** @var RoleRepository */
    protected $roleRepo;
    /** @var StaffRepository */
    protected $staffRepo;

    public function setUp()
    {
        parent::setUp();
        $this->roleRepo = new RoleDoctrineRepository($this->em);
        $this->staffRepo = new StaffDoctrineRepository($this->em);
    }

    /**
     * @test
     * @group role
     * @group rolerepo
     * @group all
     */
    public function should_return_5_roles()
    {
        $roles = $this->roleRepo->all();
        $this->assertCount(5, $roles);

        $role = $roles[0];
        $this->assertInstanceOf(Role::class, $role);
    }

    /**
     * @test
     * @group role
     * @group rolerepo
     * @group get
     */
    public function should_get_role_by_its_id()
    {
        $role = $this->getFirstRole();

        $id = $role->getId();

        $this->em->clear();

        $role = $this->roleRepo->get(NtUid::import($id));
        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals($id, $role->getId());
    }

    /**
     * @test
     * @group role
     * @group rolerepo
     * @group get
     */
    public function should_throw_error_when_role_not_found()
    {
        $this->setExpectedException(RoleNotFoundException::class);
        $fakeId = NtUid::generate(4);
        $this->roleRepo->get(NtUid::import($fakeId));
    }

    /**
     * @test
     * @group staffrole
     * @group rolerepo
     * @group get
     */
    public function should_get_staffRole()
    {
        $staff = $this->staffRepo->all();
        /** @var Staff $member */
        $member = $staff[0];

        /** @var StaffRole $sr */
        $sr = $member->allStaffRoles()[0];
        $srId = $sr->getId();

        $this->em->clear();

        $staffRole = $this->roleRepo->getStaffRole(NtUid::import($srId));

        $this->assertInstanceOf(StaffRole::class, $staffRole);
        $this->assertInstanceOf(Role::class, $staffRole->getRole());
        $this->assertInstanceOf(Staff::class, $staffRole->getStaff());

        $this->assertEquals($member->getId(), $staffRole->getStaff()->getId());
        $this->assertEquals($srId, $staffRole->getId());
    }

    /**
     * @test
     * @group staffrole
     * @group rolerepo
     * @group update
     */
    public function should_update_staffRole()
    {
        $staff = $this->staffRepo->all();
        /** @var Staff $member */
        $member = $staff[0];

        /** @var StaffRole $sr */
        $sr = $member->allStaffRoles()[0];
        $srId = $sr->getId();

        $this->em->clear();

        $now = new DateTime;

        $staffRole = $this->roleRepo->getStaffRole(NtUid::import($srId));
        $this->assertTrue($staffRole->isActive());
        $staffRole->block();

        $this->roleRepo->updateStaffRole($staffRole);

        $this->em->clear();

        $dbStaffRole = $this->roleRepo->getStaffRole(NtUid::import($srId));
        $this->assertFalse($staffRole->isActive());
    }



    /* ***************************************************
     * PRIVATE METHODS
     * **************************************************/
    /**
     * @return Role
     */
    private function getFirstRole()
    {
        $roles = $this->roleRepo->all();
        $role = $roles[0];
        return $role;
    }

}
