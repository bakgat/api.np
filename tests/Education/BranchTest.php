<?php
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\Major;
use Webpatser\Uuid\Uuid;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 14/07/16
 * Time: 22:01
 */
class BranchTest extends TestCase
{

    /**
     * @test
     * @group branch
     * @group major
     */
    public function should_create_new()
    {
        $major_name = $this->faker->word();
        $major = new Major($major_name);

        $branch_name = $this->faker->word();
        $branch = new Branch($branch_name, $major);

        $this->assertInstanceOf(Uuid::class, $major->getId());
        $this->assertEquals($major_name, $major->getName());

        $this->assertInstanceOf(Uuid::class, $branch->getId());
        $this->assertEquals($branch_name, $branch->getName());
    }


}
