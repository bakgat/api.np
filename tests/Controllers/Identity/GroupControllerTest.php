<?php
use App\Domain\Model\Identity\Group;
use App\Domain\NtUid;
use Doctrine\Common\Collections\ArrayCollection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Mockery\MockInterface;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 3/09/16
 * Time: 15:11
 */
class GroupControllerTest extends TestCase
{
    /** @var MockInterface */
    protected $groupRepo;

    public function setUp()
    {
        parent::setUp();

        $this->groupRepo = $this->mock(App\Domain\Model\Identity\GroupRepository::class);
    }

    /**
     * @test
     * @group GroupController
     */
    public function should_serialize_index()
    {

        $collection = $this->makeGroupCollection();
        $this->groupRepo->shouldReceive('all')
            ->andReturn($collection);

        $this->get('/groups')
            ->seeJsonStructure([
                '*' => [
                    'id', 'name', 'active'
                ]
            ]);
    }

    /**
     * @test
     * @group GroupController
     */
    public function should_serialize_show()
    {
        $group = $this->makeGroup();

        $this->groupRepo->shouldReceive('find')
            ->once()
            ->andReturn($group);

        $this->get('/groups/' . $group->getId())
            ->seeJsonStructure([
                'id', 'name', 'active'
            ]);
    }

    /**
     * @test
     * @group GroupController
     */
    public function should_store_success_with_active()
    {

        $data = [
            'name' => $this->faker->word,
            'active' => true
        ];

        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $this->groupRepo->shouldReceive('insert')
            ->once()
            ->andReturn();

        $this->post('/groups', $data)
            ->seeJsonStructure([
                'id',
                'name',
                'active'
            ])
            ->seeJson([
                'name' => $data['name'],
                'active' => $data['active']
            ]);
    }

    /**
     * @test
     * @group GroupController
     */
    public function should_store_success_without_active()
    {

        $data = [
            'name' => $this->faker->word
        ];

        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $this->groupRepo->shouldReceive('insert')
            ->once()
            ->andReturn();

        $this->post('/groups', $data)
            ->seeJsonStructure([
                'id',
                'name',
                'active'
            ])
            ->seeJson([
                'name' => $data['name']
            ]);
    }

    /**
     * @test
     * @group GroupController
     */
    public function should_return_422_when_store_fail()
    {

        $data = [
            'active' => true
        ];

        $message_bag = new MessageBag(['name is required']);

        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => true, 'messages' => $message_bag]));

        $this->post('/groups', $data);
        $this->assertResponseStatus(422);
    }

    /**
     * @test
     * @group GroupController
     */
    public function should_update_existing()
    {
        $group = $this->makeGroup();
        $newGroup = $this->makeGroup();
        $data = [
            'id' => (string)$group->getId(),
            'name' => $newGroup->getName(),
            'active' => $newGroup->isActive()
        ];

        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $this->groupRepo->shouldReceive('get')
            ->once()
            ->andReturn($group); //old group

        $this->groupRepo->shouldReceive('update')
            ->once()
            ->andReturn(1);

        $this->put('/groups/' . (string)$group->getId(), $data)//new Data
        ->seeJson([
            'id' => (string)$group->getId(),
            'name' => $newGroup->getName(),
            'active' => $newGroup->isActive()
        ]);
    }

    /**
     * @test
     * @group GroupController
     */
    public function should_block_existing()
    {
        $group = $this->makeGroup();

        $data = [
            'id' => (string)$group->getId(),
            'name' => $group->getName(),
            'active' => false
        ];

        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $this->groupRepo->shouldReceive('get')
            ->once()
            ->andReturn($group); //old group

        $this->groupRepo->shouldReceive('update')
            ->once()
            ->andReturn(1);

        $this->put('/groups/' . (string)$group->getId(), $data)//new Data
        ->seeJson([
            'id' => (string)$group->getId(),
            'name' => $group->getName(),
            'active' => false
        ]);
    }

    /**
     * @test
     * @group GroupController
     */
    public function should_return_422_when_update_fails()
    {
        $data = [
            'id' => (string)NtUid::generate(4),
            'active' => true
        ];

        $message_bag = new MessageBag(['name is required']);

        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => true, 'messages' => $message_bag]));

        $this->put('/groups/' . $data['id'], $data);
        $this->assertResponseStatus(422);
    }


    /**
     * @return ArrayCollection
     */
    private function makeGroupCollection()
    {
        $collection = new ArrayCollection();
        foreach (range(1, 10) as $item) {
            $group = $this->makeGroup();
            $collection->add($group);
        }
        return $collection;
    }

    /**
     * @return Group
     */
    private function makeGroup()
    {
        $group = new Group($this->faker->word);
        return $group;
    }
}
