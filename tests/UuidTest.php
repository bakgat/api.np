<?php
use App\Domain\Model\Identity\Group;
use App\Domain\NtUid;

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
        $uuid = NtUid::generate(4);

        $this->assertInstanceOf(NtUid::class, $uuid);
        $this->assertCount(5, explode('-', $uuid->toString()));
    }

    /**
     * @test
     * @group uuid
     */
    public function should_import_from_string()
    {
        $id = $this->faker->uuid;
        $uuid = NtUid::import($id);

        $this->assertInstanceOf(NtUid::class, $uuid);
    }

    /**
     * @test
     * @group uuid
     */
    public function should_generate_new()
    {
        $uuidV1 = NtUid::generate(1);
        $uuidV3 = NtUid::generate(3, $this->faker->word, NtUid::NS_DNS);
        $uuidV4 = NtUid::generate(4);
        $uuidV5 = NtUid::generate(5, $this->faker->word, NtUid::NS_DNS);

        $this->assertInstanceOf(NtUid::class, $uuidV1);
        $this->assertInstanceOf(NtUid::class, $uuidV3);
        $this->assertInstanceOf(NtUid::class, $uuidV4);
        $this->assertInstanceOf(NtUid::class, $uuidV5);
    }

    /**
     * @test
     * @group uuid
     */
    public function should_throw_on_version_2()
    {
        $this->setExpectedException(Exception::class);
        $uuidV2 = NtUid::generate(2);
    }
    /**
     * @test
     * @group uuid
     */
    public function should_throw_on_wrong_version()
    {
        $this->setExpectedException(Exception::class);
        $uuidV2 = NtUid::generate(6);
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
