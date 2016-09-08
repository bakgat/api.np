<?php
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

}
