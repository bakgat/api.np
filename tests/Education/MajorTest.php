<?php
use App\Domain\Model\Education\Major;
use App\Domain\NtUid;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 15/07/16
 * Time: 06:30
 */
class MajorTest extends TestCase
{
    /**
     * @test
     * @group major
     * @group education
     */
    public function should_create_new()
    {
        $major_name = $this->faker->word();
        $major = new Major($major_name);

        $this->assertInstanceOf(NtUid::class, $major->getId());
        $this->assertEquals($major_name, $major->getName());
    }


}
