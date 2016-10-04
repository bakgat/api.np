<?php
use App\Domain\Model\Education\BranchForGroup;
use App\Domain\Model\Evaluation\Evaluation;
use App\Domain\Model\Evaluation\EvaluationRepository;
use App\Domain\Model\Evaluation\Exceptions\EvaluationNotFoundException;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\GroupRepository;
use App\Domain\Model\Identity\StudentRepository;
use App\Domain\NtUid;
use App\Repositories\Evaluation\EvaluationDoctrineRepository;
use App\Repositories\Identity\GroupDoctrineRepository;
use App\Repositories\Identity\StudentDoctrineRepository;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 8/08/16
 * Time: 14:02
 */
class EvaluationDoctrineRepositoryTest extends DoctrineTestCase
{
    /** @var GroupRepository */
    protected $groupRepo;
    /** @var  StudentRepository */
    protected $studentRepo;
    /** @var  EvaluationRepository */
    protected $evaluationRepo;

    public function setUp()
    {
        parent::setUp();

        $this->groupRepo = new GroupDoctrineRepository($this->em);
        $this->evaluationRepo = new EvaluationDoctrineRepository($this->em);
        $this->studentRepo = new StudentDoctrineRepository($this->em);
    }

    /**
     * @test
     * @group evaluation
     * @group evaluationrepo
     * @group ingroup
     */
    public function should_get_result_for_every_student_in_group()
    {
        $groups = $this->groupRepo->allActive();
        $group = $groups[0];

        $students = $this->studentRepo->allActiveInGroup($group);

        $evaluations = $this->evaluationRepo->allEvaluationsForGroup($group);
        /** @var Evaluation $evaluation */
        foreach ($evaluations as $evaluation) {
            $this->assertCount(count($evaluation->getResults()), $students);
        }
    }

    /**
     * @test
     * @group evaluation
     * @group evaluationrepo
     * @group get
     */
    public function should_get_evaluation()
    {
        $groups = $this->groupRepo->allActive();

        /** @var Group $group */
        $group = $groups[0];

        $evaluations = $this->evaluationRepo->allEvaluationsForGroup($group);
        /** @var Evaluation $evaluation */
        $evaluation = $evaluations[0];
        $id = $evaluation->getId();

        $this->em->clear();

        $dbEvaluation = $this->evaluationRepo->get(NtUid::import($id));
        $this->assertInstanceOf(Evaluation::class, $dbEvaluation);
        $this->assertEquals($id, $dbEvaluation->getId());
    }

    /**
     * @test
     * @group evaluation
     * @group evaluationrepo
     * @group get
     */
    public function should_throw_when_no_evaluation_found()
    {
        $this->setExpectedException(EvaluationNotFoundException::class);
        $fakeId = NtUid::generate(4);

        $this->evaluationRepo->get($fakeId);
    }

    /**
     * @test
     * @group evaluation
     * @group evaluationrepo
     * @group insert
     */
    public function should_insert_evaluation()
    {
        $groups = $this->groupRepo->allActive();
        /** @var Group $group */
        $group = $groups[0];
        /** @var BranchForGroup $bfg */
        $bfg = $group->getBranchForGroups()[0];

        $max = null;
        if($bfg->getEvaluationType()->getValue() == 'P') {
            $max = $this->faker->biasedNumberBetween(10, 100);
        }
        $title = $this->faker->word;
        $now = new DateTime;

        $evaluation = new Evaluation($bfg, $title, $now, $max);

        $id = $this->evaluationRepo->insert($evaluation);

        $this->em->clear();

        $savedEv = $this->evaluationRepo->get(NtUid::import($id));
        $this->assertInstanceOf(Evaluation::class, $savedEv);

        $this->assertEquals($id, $savedEv->getId());
        $this->assertEquals($title, $savedEv->getTitle());
        $this->assertEquals($now->format('Y-m-d'), $savedEv->getDate()->format('Y-m-d'));

    }


    /* ***************************************************
     * PRIVATE METHODS
     * **************************************************/
    private function makeStudentCollection()
    {
        $collection = new ArrayCollection();
        foreach (range(1, 10) as $item) {
            $student = $this->makeStudent();
            $collection->add($student);
        }
        return $collection;
    }

    private function makeStudent()
    {
        $fn = $this->faker->firstName;
        $ln = $this->faker->lastName;
        $schoolId = $this->faker->bankAccountNumber;
        $birthday = $this->faker->dateTime;
        $gender = $this->faker->randomElement(Gender::values());

        $student = new Student($fn, $ln, $schoolId, $gender, $birthday);

        foreach (range(1, 3) as $item) {
            $r = $this->faker->randomElement(Redicodi::values());
            $b = $this->makeBranch();
            $student->addRedicodi($r, $b, null, $this->faker->text(120));
        }

        return $student;
    }

    /**
     * @return ArrayCollection
     */
    private function makeGroupCollection()
    {
        $collection = new ArrayCollection();
        foreach (range(1, 10) as $item) {
            $group = $this->makeGroup();
            $collection->add($group);
        }
        return $collection;
    }

    /**
     * @return Group
     */
    private function makeGroup()
    {
        $group = new Group($this->faker->word);
        return $group;
    }

    private function makeBranch()
    {
        $branch = new Branch($this->faker->word);
        $major = new Major($this->faker->word);
        $major->addBranch($branch);
        return $branch;
    }

    private function makeEvaluation($group)
    {
        $branch = $this->makeBranch();
        $now = new DateTime;
        $start = clone $now->modify('-1 year');
        $dr = ['start' => $start];
        $evType = new EvaluationType(EvaluationType::POINT);
        $max = $this->faker->biasedNumberBetween(10, 100);

        $title = $this->faker->word;
        $evMax = $this->faker->biasedNumberBetween(20, 200);


        $branchForGroup = new BranchForGroup($branch, $group, $dr, $evType, $max);
        $ev = new Evaluation($branchForGroup, $title, $now, $evMax);
        return $ev;
    }

    private function makeEvaluations($group)
    {
        $collection = new ArrayCollection();
        foreach (range(1, 20) as $item) {
            $evaluation = $this->makeEvaluation($group);
            $collection->add($evaluation);
        }
        return $collection;
    }

}
