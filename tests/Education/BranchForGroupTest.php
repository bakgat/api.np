<?php
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\BranchForGroup;
use App\Domain\Model\Education\Major;
use App\Domain\Model\Evaluation\EvaluationType;
use App\Domain\Model\Identity\Group;
use Webpatser\Uuid\Uuid;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 15/07/16
 * Time: 07:06
 */
class BranchForGroupTest extends TestCase
{
    /**
     * @test
     * @group education
     * @group Branch
     * @group Group
     */
    public function should_create_new_with_point()
    {
        $major_name = $this->faker->unique()->word();
        $branch_name = $this->faker->unique()->word();
        $group_name = $this->faker->unique()->word();
        $evaluationType = new EvaluationType(EvaluationType::POINT);
        $max = 20;

        $major = new Major($major_name);
        $branch = new Branch($branch_name);
        $branch->joinMajor($major);

        $group = new Group($group_name);

        $bfg = new BranchForGroup($branch, $group, $evaluationType, $max);

        $this->assertInstanceOf(BranchForGroup::class, $bfg);
        $this->assertInstanceOf(Uuid::class, $bfg->getId());
        $this->assertInstanceOf(Branch::class, $bfg->getBranch());
        $this->assertInstanceOf(Group::class, $bfg->getGroup());

        $this->assertEquals($max, $bfg->getMax());
    }

}
