<?php
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\BranchRepository;
use App\Domain\Model\Education\Major;
use App\Domain\Model\Identity\GroupRepository;
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
        $groups  = $this->groupRepo->all();
        $branches = $this->branchRepo->all($groups[0]);
        $id = $branches[0]->getId();
        $this->em->clear();

        $branch = $this->branchRepo->findBranch($id);
        $this->assertInstanceOf(Branch::class, $branch);
        $this->assertEquals($branch->getId(), $id);
    }
}
