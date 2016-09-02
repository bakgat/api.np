<?php
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Staff;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 2/09/16
 * Time: 14:30
 */
class GenderTest extends TestCase
{
    /**
     * @test
     * @group gender
     */
    public function should_create_new()
    {
        $gender = new Gender(Gender::FEMALE);

        $this->assertInstanceOf(Gender::class, $gender);
        $this->assertEquals('F', (string)$gender);
    }

    /**
     * @test
     * @group gender
     */
    public function should_serialize_to_json()
    {
        $fn = $this->faker->firstName;
        $ln = $this->faker->lastName;
        $gender = new Gender(Gender::MALE);
        $email = $this->faker->email;

        //use object to haven an root object for jms serialization
        $staff = new Staff($fn, $ln, $email, $gender);

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        $serialized = $serializer->serialize($staff, 'json');

        $expected = '"gender":"M"';

        $this->assertContains($expected, $serialized);
    }
}
