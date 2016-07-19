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
    public function should_return_between_10_and_17_distinct_branches_per_group()
    {
        $groups = $this->groupRepo->all();
        foreach ($groups as $group) {
            $count = 0;
            $majors = $this->branchRepo->all($group);
            foreach ($majors as $major) {
                foreach ($major->getBranches() as $branch) {
                    $count++;
                }
            }
            $this->assertGreaterThanOrEqual(10, $count);
            $this->assertLessThanOrEqual(17, $count);
        }
    }
}
