<?php
use App\Domain\Model\Identity\Exceptions\GroupNotFoundException;
use App\Domain\Model\Identity\Exceptions\NonUniqueGroupNameException;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\GroupRepository;
use App\Repositories\Identity\GropuDoctrineRepository;
use App\Repositories\Identity\GroupDoctrineRepository;
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

        $this->groupRepo = new GroupDoctrineRepository($this->em);
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

    /**
     * @test
     * @group group
     * @group grouprepo
     * @group insert
     */
    public function should_throw_not_unique_on_insert()
    {
        $groups = $this->groupRepo->all();
        $group = $groups[0];

        $this->em->clear();

        $non_unique_group = new Group($group->getName());

        $this->setExpectedException(NonUniqueGroupNameException::class);
        $this->groupRepo->insert($non_unique_group);
    }

    /**
     * @test
     * @group group
     * @group grouprepo
     * @group update
     */
    public function should_update_existing_group()
    {
        $name = 'fake_word_unique_' . $this->faker->uuid;

        $group = new Group($name);
        $id = $this->groupRepo->insert($group);

        $this->em->clear();

        $dbGroup = $this->groupRepo->get($id);
        $updateName = $name . $this->faker->uuid;
        $dbGroup->updateName($updateName);
        $count = $this->groupRepo->update($dbGroup);

        $this->em->clear();

        $savedGroup = $this->groupRepo->get($id);

        $this->assertInstanceOf(Group::class, $dbGroup);
        $this->assertInstanceOf(Group::class, $savedGroup);

        $this->assertEquals(1, $count);

        $this->assertNotEquals($group->getName(), $savedGroup->getName());
        $this->assertEquals($group->getId(), $savedGroup->getId());

        $this->assertEquals($dbGroup->getId(), $savedGroup->getId());
        $this->assertEquals($savedGroup->getName(), $updateName);
        $this->assertEquals($dbGroup->getName(), $savedGroup->getName());
    }



    /**
     * @test
     * @group group
     * @group grouprepo
     * @group delete
     */
    public function should_delete_existing_group()
    {
        $name = 'fake_word_unique_' . $this->faker->uuid;

        $group = new Group($name);
        $id = $this->groupRepo->insert($group);

        $this->em->clear();

        $savedGroup = $this->groupRepo->get($id);
        $count = $this->groupRepo->delete($id);

        $this->em->clear();

        $removedGroup = $this->groupRepo->find($id);

        $this->assertEquals($savedGroup->getId(), $id);
        $this->assertEquals(1, $count);
        $this->assertNull($removedGroup);

    }
}
