<?php
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\BranchForGroup;
use App\Domain\Model\Education\BranchRepository;
use App\Domain\Model\Education\Exceptions\BranchNotFoundException;
use App\Domain\Model\Education\Exceptions\MajorNotFoundException;
use App\Domain\Model\Education\Major;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\GroupRepository;
use App\Domain\NtUid;
use App\Repositories\Education\BranchDoctrineRepository;
use App\Repositories\Identity\GroupDoctrineRepository;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/07/16
 * Time: 21:19
 */
class BranchDoctrineRepositoryTest extends DoctrineTestCase
{
    /** @var GroupRepository */
    protected $groupRepo;
    /** @var BranchRepository */
    protected $branchRepo;

    public function setUp()
    {
        parent::setUp();

        $this->groupRepo = new GroupDoctrineRepository($this->em);
        $this->branchRepo = new BranchDoctrineRepository($this->em);
    }


    /**
     * @test
     * @group group
     * @group branch
     * @group find
     */
    public function should_find_existing_branch_by_its_id()
    {
        $groups = $this->groupRepo->all();
        $majors = $this->branchRepo->all($groups[0]);
        $branches = $majors[0]->getBranches();
        $id = NtUid::import($branches[0]->getId());
        $this->em->clear();

        $branch = $this->branchRepo->findBranch($id);
        $this->assertInstanceOf(Branch::class, $branch);
        $this->assertEquals($branch->getId(), $id);
    }

    /**
     * @test
     * @group group
     * @group branch
     * @group find
     */
    public function should_return_null_when_no_branch_found()
    {
        $fakeId = NtUid::generate(4);
        $branch = $this->branchRepo->findBranch($fakeId);
        $this->assertNull($branch);
    }

    /**
     * @test
     * @group group
     * @group branch
     * @group get
     */
    public function should_get_branch_by_its_id()
    {
        $groups = $this->groupRepo->all();
        $majors = $this->branchRepo->all($groups[0]);
        $branches = $majors[0]->getBranches();
        $id = NtUid::import($branches[0]->getId());
        $this->em->clear();

        $branch = $this->branchRepo->getBranch($id);
        $this->assertInstanceOf(Branch::class, $branch);
        $this->assertEquals($branch->getId(), $id);
    }

    /**
     * @test
     * @group group
     * @group branch
     * @group get
     */
    public function should_throw_when_get_branch_fails()
    {
        $this->setExpectedException(BranchNotFoundException::class);
        $fakeId = NtUid::generate(4);
        $this->branchRepo->getBranch($fakeId);
    }

    /**
     * @test
     * @group group
     * @group major
     * @group find
     */
    public function should_find_existing_major_by_its_id()
    {
        $groups = $this->groupRepo->all();
        $majors = $this->branchRepo->all($groups[0]);
        $id = NtUid::import($majors[0]->getId());

        $this->em->clear();

        $major = $this->branchRepo->findMajor($id);

        $this->assertInstanceOf(Major::class, $major);
        $this->assertEquals($major->getId(), $id);
    }

    /**
     * @test
     * @group group
     * @group major
     * @group find
     */
    public function should_return_null_when_no_major_is_found()
    {
        $fakeId = NtUid::generate(4);
        $major = $this->branchRepo->findMajor($fakeId);
        $this->assertNull($major);
    }


    /**
     * @test
     * @group group
     * @group major
     * @group get
     */
    public function should_get_major_by_its_id()
    {
        $groups = $this->groupRepo->all();
        $majors = $this->branchRepo->all($groups[0]);
        $id = NtUid::import($majors[0]->getId());

        $this->em->clear();

        $major = $this->branchRepo->getMajor($id);

        $this->assertInstanceOf(Major::class, $major);
        $this->assertEquals($major->getId(), $id);
    }

    /**
     * @test
     * @group major
     * @group get
     */
    public function should_throw_when_get_major_fails()
    {
        $this->setExpectedException(MajorNotFoundException::class);
        $fakeId = NtUid::generate(4);
        $this->branchRepo->getMajor($fakeId);
    }

    /**
     * @test
     * @group branch
     * @group major
     * @group insert
     */
    public function should_insert_new_major_and_branch()
    {
        $branch_name = 'fake_unique_branch_' . $this->faker->uuid;
        $major_name = 'fake_unique_major_' . $this->faker->uuid;

        $major = new Major($major_name);
        $branch = new Branch($branch_name);
        $major->addBranch($branch);

        $id = $this->branchRepo->insertMajor($major);

        $this->em->clear();

        $dbMajor = $this->branchRepo->getMajor($id);
        $dbBranch = $dbMajor->getBranches()[0];

        $this->assertInstanceOf(Branch::class, $dbBranch);
        $this->assertInstanceOf(Major::class, $dbMajor);

        $this->assertEquals($branch->getName(), $dbBranch->getName());
        $this->assertEquals($major->getId(), $id);
        $this->assertEquals($dbMajor->getName(), $major->getName());
    }

    /**
     * @test
     * @group branch
     * @group major
     * @group update
     */
    public function should_insert_new_branch_on_existing_major()
    {
        $groups = $this->groupRepo->all();
        $majors = $this->branchRepo->all($groups[0]);
        /** @var Major $major */
        $major = $majors[0];

        $major_id = NtUid::import($major->getId());
        $branch_count = count($major->getBranches());

        $branch_name = 'fake_unique_branch_' . $this->faker->uuid;
        $branch = new Branch($branch_name);
        $major->addBranch($branch);

        $rows = $this->branchRepo->update($major);

        $this->em->clear();

        $dbMajor = $this->branchRepo->getMajor($major_id);
        $dbBranch = null;
        foreach ($dbMajor->getBranches() as $branch) {
            if ($branch->getName() == $branch_name) {
                $dbBranch = $branch;
            }
        }

        $this->assertEquals(1, $rows);
        $this->assertNotNull($dbBranch);
        $this->assertInstanceOf(Branch::class, $dbBranch);
        //TODO: fix this !
        //$this->assertEquals($branch_count + 1, count($dbMajor->getBranches()));
    }

    /**
     * @test
     * @group branch
     * @group group
     * @group get
     */
    public function should_get_all_branches_in_group()
    {
        $groups = $this->groupRepo->allActive();
        /** @var Group $group */
        $group = $groups[0];

        $branches = $this->branchRepo->allBranchesInGroup($group);
        $this->assertGreaterThan(0, count($branches));

        /** @var BranchForGroup $first */
        $first = $branches[0];
        $this->assertInstanceOf(BranchForGroup::class, $first);
        $this->assertEquals($group, $first->getGroup());
    }

    /**
     * @test
     * @group branch
     * @group group
     * @group get
     */
    public function should_get_branch_for_group()
    {
        $groups = $this->groupRepo->allActive();
        /** @var Group $group */
        $group = $groups[0];

        $branches = $this->branchRepo->allBranchesInGroup($group);

        /** @var BranchForGroup $first */
        $first = $branches[0];
        $id = $first->getId();

        $this->em->clear();

        $branchForGroup = $this->branchRepo->getBranchForGroup(NtUid::import($id));
        $this->assertInstanceOf(BranchForGroup::class, $branchForGroup);
        $this->assertEquals($id, $branchForGroup->getId());
    }


}
