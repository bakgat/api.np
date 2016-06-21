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
    public function should_create_new() {
        $name = 'TestGroup';

        $group = new Group($name);

        $this->assertInstanceOf(Uuid::class, $group->getId());
        $this->assertCount(5, explode('-', $group->getId()));
        $this->assertEquals($name, $group->getName());
    }
}
