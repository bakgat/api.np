<?php
use App\Domain\Model\Identity\Group;
use App\Domain\Uuid;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 1/09/16
 * Time: 21:45
 */
class UuidTest extends TestCase
{
    /**
     * @test
     * @group uuid
     */
    public function should_create_new()
    {
        $uuid = Uuid::generate(4);

        $this->assertInstanceOf(Uuid::class, $uuid);
        $this->assertCount(5, explode('-', (string)$uuid));
    }

    /**
     * @test
     * @group uuid
     */
    public function should_serialize_to_json()
    {
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        // Use Group class that make use of UUID.
        // Because Uuid must have a root object to serialize on.
        $group = new Group($this->faker->word);

        $serialized = $serializer->serialize($group, 'json');
        $expected = '"id":"' . (string)$group->getId() . '"';

        $this->assertContains($expected, $serialized);
    }
}
