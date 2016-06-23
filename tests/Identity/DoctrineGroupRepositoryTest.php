<?php
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\GroupRepository;
use App\Repositories\Identity\DoctrineGroupRepository;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 23/06/16
 * Time: 16:21
 */
class DoctrineGroupRepositoryTest extends DoctrineTestCase
{
    /** @var GroupRepository */
    protected $groupRepo;

    public function setUp()
    {
        parent::setUp();

        $this->groupRepo = new DoctrineGroupRepository($this->em);
    }

    /**
     * @test
     * @group group
     * @group grouprepo
     * @group all
     */
    public function should_return_20_groups()
    {
        $groups = $this->groupRepo->all();
        $this->assertCount(20, $groups);
    }

    /**
     * @test
     * @group group
     * @group grouprepo
     * @group find
     */
    public function should_find_group_by_its_id()
    {
        $groups = $this->groupRepo->all();
        $id = $groups[0]->getId();

        $this->em->clear();

        $group = $this->groupRepo->find($id);

        $this->assertInstanceOf(Group::class, $group);
        $this->assertEquals($group->getId(), $id);
    }
}
