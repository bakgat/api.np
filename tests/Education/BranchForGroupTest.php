<?php
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\BranchForGroup;
use App\Domain\Model\Education\Exceptions\MaxNullException;
use App\Domain\Model\Education\Major;
use App\Domain\Model\Evaluation\EvaluationType;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Time\DateRange;
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


        $evaluationType = new EvaluationType(EvaluationType::POINT);
        $daterange = ['start' => new DateTime];
        $max = 20;

        $branch = $this->makeBranch();
        $group = $this->makeGroup();

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

        $daterange = ['start' => new DateTime];
        $evaluationType = new EvaluationType(EvaluationType::POINT);


        $branch = $this->makeBranch();
        $group = $this->makeGroup();

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
        $daterange = ['start' => new DateTime];
        $evaluationType = new EvaluationType(EvaluationType::COMPREHENSIVE);

        $branch = $this->makeBranch();
        $group = $this->makeGroup();

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
    public function should_not_set_max_on_not_points()
    {
        $daterange = ['start' => new DateTime];
        $evaluationType = new EvaluationType(EvaluationType::COMPREHENSIVE);
        $max = 20;

        $branch = $this->makeBranch();
        $group = $this->makeGroup();

        $bfg = new BranchForGroup($branch, $group, $daterange, $evaluationType, $max);

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
    public function should_change_max()
    {
        $branch = $this->makeBranch();
        $group = $this->makeGroup();

        $daterange = ['start' => new DateTime];
        $evaluationType = new EvaluationType(EvaluationType::POINT);
        $max = 20;

        $bfg = new BranchForGroup($branch, $group, $daterange, $evaluationType, $max);

        $this->assertEquals($max, $bfg->getMax());

        $bfg->changeMax(30);
        $this->assertEquals(30, $bfg->getMax());
    }

    /**
     * @test
     * @group education
     * @group branch
     * @group group
     */
    public function should_leave_group_and_only_once()
    {
        $now = new DateTime;
        $end = clone $now->modify('-1 day');

        $branch = $this->makeBranch();
        $group = $this->makeGroup();

        $daterange = ['start' => new DateTime];
        $evaluationType = new EvaluationType(EvaluationType::COMPREHENSIVE);

        $bfg = new BranchForGroup($branch, $group, $daterange, $evaluationType);

        $this->assertEquals(DateRange::FUTURE, $bfg->isActiveUntil()->format('Y-m-d'));
        $bfg->leaveGroup();

        $this->assertEquals($end->format('Y-m-d'), $bfg->isActiveUntil()->format('Y-m-d'));

        $far = clone $now->modify('-1 year');
        $bfg->leaveGroup($far);

        //far must not be the new end date
        //group was already left
        $this->assertNotEquals($far->format('Y-m-d'), $bfg->isActiveUntil()->format('Y-m-d'));
        $this->assertEquals($end->format('Y-m-d'), $bfg->isActiveUntil()->format('Y-m-d'));
    }

    /**
     * @test
     * @group education
     * @group branch
     * @group group
     */
    public function should_leave_group_at_certain_date()
    {
        $now = new DateTime;
        $end = clone $now->modify('-1 year');

        $branch = $this->makeBranch();
        $group = $this->makeGroup();

        $daterange = ['start' => new DateTime];
        $evaluationType = new EvaluationType(EvaluationType::COMPREHENSIVE);

        $bfg = new BranchForGroup($branch, $group, $daterange, $evaluationType);

        $this->assertEquals(DateRange::FUTURE, $bfg->isActiveUntil()->format('Y-m-d'));
        $bfg->leaveGroup($end);
        $this->assertEquals($end->format('Y-m-d'), $bfg->isActiveUntil()->format('Y-m-d'));

    }


    /**
     * @return Branch
     */
    private function makeBranch()
    {
        $major_name = $this->faker->unique()->word();
        $branch_name = $this->faker->unique()->word();
        $major = new Major($major_name);
        $branch = new Branch($branch_name);
        $branch->joinMajor($major);
        return $branch;
    }

    /**
     * @return Group
     */
    private function makeGroup()
    {
        $group_name = $this->faker->unique()->word();
        $group = new Group($group_name);
        return $group;
    }


}
