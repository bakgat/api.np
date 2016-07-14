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

    /**
     * @test
     * @group branch
     * @group major
     */
    public function should_update_existing_branch()
    {
        $major_name = $this->faker->word();
        $major = new Major($major_name);

        $branch_name = $this->faker->unique()->word();
        $branch = new Branch($branch_name, $major);


        $new_branch_name = $this->faker->unique()->word();
        $branch->changeName($new_branch_name);

        $this->assertNotEquals($branch_name, $branch->getName());
        $this->assertEquals($new_branch_name, $branch->getName());
    }
}
