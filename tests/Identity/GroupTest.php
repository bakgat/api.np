<?php
use App\Domain\Model\Identity\Group;
use Webpatser\Uuid\Uuid;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 21/06/16
 * Time: 08:08
 */
class GroupTest extends TestCase
{
    /**
     * @test
     * @group group
     */
    public function should_create_new()
    {
        $name = $this->faker->word();

        $group = new Group($name);

        $this->assertInstanceOf(Uuid::class, $group->getId());
        $this->assertCount(5, explode('-', $group->getId()));
        $this->assertEquals($name, $group->getName());
        $this->assertTrue($group->isActive());
    }

    /**
     * @test
     * @group group
     */
    public function should_activate_and_block_a_group()
    {
        $name = $this->faker->word();

        $group = new Group($name, true);

        $this->assertTrue($group->isActive());

        $group->block();
        $this->assertFalse($group->isActive());

        $group->activate();
        $this->assertTrue($group->isActive());
    }
}
