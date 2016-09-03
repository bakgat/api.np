<?php
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\Major;
use App\Domain\Model\Identity\Group;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 3/09/16
 * Time: 22:55
 */
class BranchControllerTest extends TestCase
{
    /** @var MockInterface */
    protected $branchRepo;
    /** @var MockInterface */
    protected $groupRepo;

    public function setUp()
    {
        parent::setUp();
        $this->branchRepo = $this->mock(App\Domain\Model\Education\BranchRepository::class);
        $this->groupRepo = $this->mock(App\Domain\Model\Identity\GroupRepository::class);
    }

    /**
     * @test
     * @group BranchController
     */
    public function should_index_with_group()
    {
        $group = $this->makeGroup();
        $this->groupRepo->shouldReceive('get')
            ->once()
            ->andReturn($group);

        $groupId = (string)$group->getId();

        $this->branchRepo->shouldReceive('all')
            ->once()
            ->andReturn($this->makeBranchCollection());

        $this->get('/branches?group=' . $groupId)
            ->seeJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'branches' => [
                        '*' => [
                            'id',
                            'name'
                        ]
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @group BranchController
     */
    public function should_return_null_with_no_group()
    {
        $this->get('/branches')
            ->assertResponseStatus(500);
    }


    /**
     * @return Group
     */
    private function makeGroup()
    {
        $group = new Group($this->faker->word);
        return $group;
    }

    private function makeBranchCollection()
    {
        $majors = new ArrayCollection();
        foreach (range(1, 2) as $item) {
            $major = new Major($this->faker->unique()->word);
            foreach (range(1, 10) as $item1) {
                $branch = new Branch($this->faker->unique()->word);
                $major->addBranch($branch);
            }
            $majors->add($major);
        }
        return $majors;
    }
}
