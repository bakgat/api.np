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
        $branches = [];
        foreach ($groups as $group) {
            $majors = $this->branchRepo->all($group);
            foreach ($majors as $major) {
                foreach ($major->getBranches() as $branch) {
                    if (!in_array($branch->getId(), $branches)) {
                        array_push($branches, (string)$branch->getId());
                    }
                }
            }
        }
        $this->assertCount(50, $branches);
    }

    public function should_find_existing_branch()
    {

    }
}
