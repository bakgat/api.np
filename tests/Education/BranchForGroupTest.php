<?php
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\BranchForGroup;
use App\Domain\Model\Education\Exceptions\MaxNullException;
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
        $daterange = ['start' => new DateTime];
        $max = 20;

        $major = new Major($major_name);
        $branch = new Branch($branch_name);
        $branch->joinMajor($major);

        $group = new Group($group_name);

        $bfg = new BranchForGroup($branch, $group, $daterange, $evaluationType, $max);

        $this->assertInstanceOf(BranchForGroup::class, $bfg);
        $this->assertInstanceOf(Uuid::class, $bfg->getId());
        $this->assertInstanceOf(Branch::class, $bfg->getBranch());
        $this->assertInstanceOf(Group::class, $bfg->getGroup());

        $this->assertEquals($max, $bfg->getMax());
    }

    /**
     * @test
     * @group education
     * @group branch
     * @group group
     */
    public function should_throw_when_point_and_max_null()
    {
        $this->setExpectedException(MaxNullException::class);
        $major_name = $this->faker->unique()->word();
        $branch_name = $this->faker->unique()->word();
        $group_name = $this->faker->unique()->word();
        $daterange = ['start' => new DateTime];
        $evaluationType = new EvaluationType(EvaluationType::POINT);

        $major = new Major($major_name);
        $branch = new Branch($branch_name);
        $branch->joinMajor($major);

        $group = new Group($group_name);

        $bfg = new BranchForGroup($branch, $group, $daterange, $evaluationType);
    }

    /**
     * @test
     * @group education
     * @group branch
     * @group group
     */
    public function should_allow_null_max_on_not_points()
    {
        $major_name = $this->faker->unique()->word();
        $branch_name = $this->faker->unique()->word();
        $group_name = $this->faker->unique()->word();
        $daterange = ['start' => new DateTime];
        $evaluationType = new EvaluationType(EvaluationType::COMPREHENSIVE);

        $major = new Major($major_name);
        $branch = new Branch($branch_name);
        $branch->joinMajor($major);

        $group = new Group($group_name);

        $bfg = new BranchForGroup($branch, $group, $daterange, $evaluationType);

        $this->assertInstanceOf(BranchForGroup::class, $bfg);
        $this->assertInstanceOf(Uuid::class, $bfg->getId());
        $this->assertInstanceOf(Branch::class, $bfg->getBranch());
        $this->assertInstanceOf(Group::class, $bfg->getGroup());

        $this->assertNull($bfg->getMax());
    }

    /**
     * @test
     * @group education
     * @group branch
     * @group group
     */
    public function should_not_set_max_on_not_points() {
        $major_name = $this->faker->unique()->word();
        $branch_name = $this->faker->unique()->word();
        $group_name = $this->faker->unique()->word();
        $daterange = ['start' => new DateTime];
        $evaluationType = new EvaluationType(EvaluationType::COMPREHENSIVE);
        $max = 20;

        $major = new Major($major_name);
        $branch = new Branch($branch_name);
        $branch->joinMajor($major);

        $group = new Group($group_name);

        $bfg = new BranchForGroup($branch, $group, $daterange, $evaluationType, $max);

        $this->assertInstanceOf(BranchForGroup::class, $bfg);
        $this->assertInstanceOf(Uuid::class, $bfg->getId());
        $this->assertInstanceOf(Branch::class, $bfg->getBranch());
        $this->assertInstanceOf(Group::class, $bfg->getGroup());

        $this->assertNull($bfg->getMax());
    }
}
