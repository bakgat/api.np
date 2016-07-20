<?php
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\BranchRepository;
use App\Domain\Model\Education\Exceptions\BranchNotFoundException;
use App\Domain\Model\Education\Major;
use App\Domain\Model\Identity\GroupRepository;
use App\Repositories\Education\BranchDoctrineRepository;
use App\Repositories\Identity\GroupDoctrineRepository;
use Webpatser\Uuid\Uuid;

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
     * @group major
     * @group all
     */
    public function should_return_between_50_distinct_branches()
    {
        $groups = $this->groupRepo->all();
        $br = [];
        foreach ($groups as $group) {
            $branches = $this->branchRepo->all($group);
            foreach ($branches as $branch) {
                array_push($br, $branch->getId());
            }
        }
        $br = array_unique($br);
        $this->assertCount(50, $br);
    }

    /**
     * @test
     * @group group
     * @group branch
     * @group major
     * @group find
     */
    public function should_find_existing_branch_by_its_id()
    {
        $groups = $this->groupRepo->all();
        $branches = $this->branchRepo->all($groups[0]);
        $id = Uuid::import($branches[0]->getId());
        $this->em->clear();

        $branch = $this->branchRepo->findBranch($id);
        $this->assertInstanceOf(Branch::class, $branch);
        $this->assertEquals($branch->getId(), $id);
    }

    /**
     * @test
     * @group group
     * @group branch
     * @group major
     * @group find
     */
    public function should_return_null_when_no_branch_found()
    {
        $fakeId = Uuid::generate(4);
        $branch = $this->branchRepo->findBranch($fakeId);
        $this->assertNull($branch);
    }

    /**
     * @test
     * @group group
     * @group branch
     * @group major
     * @group get
     */
    public function should_get_branch_by_its_id()
    {
        $groups = $this->groupRepo->all();
        $branches = $this->branchRepo->all($groups[0]);
        $id = Uuid::import($branches[0]->getId());
        $this->em->clear();

        $branch = $this->branchRepo->getBranch($id);
        $this->assertInstanceOf(Branch::class, $branch);
        $this->assertEquals($branch->getId(), $id);
    }

    /**
     * @test
     * @group group
     * @group branch
     * @group major
     * @group get
     */
    public function should_throw_when_get_branch_fails()
    {
        $this->setExpectedException(BranchNotFoundException::class);
        $fakeId = Uuid::generate(4);
        $branch = $this->branchRepo->getBranch($fakeId);
    }
}
