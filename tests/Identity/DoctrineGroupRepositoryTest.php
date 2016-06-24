<?php
use App\Domain\Model\Identity\Exceptions\GroupNotFoundException;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\GroupRepository;
use App\Repositories\Identity\DoctrineGroupRepository;
use Webpatser\Uuid\Uuid;

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

    /**
     * @test
     * @group group
     * @group grouprepo
     * @group find
     */
    public function should_return_null_when_no_group_found()
    {
        $fakeId = Uuid::generate(4);
        $group = $this->groupRepo->find($fakeId);
        $this->assertNull($group);
    }

    /**
     * @test
     * @group group
     * @group grouprepo
     * @group get
     */
    public function should_get_group_its_id()
    {
        $groups = $this->groupRepo->all();
        $id = $groups[0]->getId();

        $this->em->clear();

        $group = $this->groupRepo->get($id);

        $this->assertInstanceOf(Group::class, $group);
        $this->assertEquals($group->getId(), $id);
    }

    /**
     * @test
     * @group group
     * @group grouprepo
     * @group get
     */
    public function should_throw_when_get_group_fails()
    {
        $this->setExpectedException(GroupNotFoundException::class);
        $fakeId = Uuid::generate(4);
        $group = $this->groupRepo->get($fakeId);
    }

    /**
     * @test
     * @group group
     * @group grouprepo
     * @group insert
     */
    public function should_insert_new_group()
    {
        $name = 'fake_word_unique_' . $this->faker->uuid;

        $group = new Group($name);
        $id = $this->groupRepo->insert($group);

        $this->em->clear();

        $dbGroup = $this->groupRepo->get($id);

        $this->assertInstanceOf(Group::class, $dbGroup);
        $this->assertEquals($dbGroup->getName(), $group->getName());
        $this->assertEquals($dbGroup->getId(), $group->getId());
    }


}
