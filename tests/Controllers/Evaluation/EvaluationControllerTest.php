<?php
use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\BranchForGroup;
use App\Domain\Model\Education\Major;
use App\Domain\Model\Education\Redicodi;
use App\Domain\Model\Evaluation\Evaluation;
use App\Domain\Model\Evaluation\EvaluationType;
use App\Domain\Model\Evaluation\PointResult;
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Student;
use App\Domain\Services\Evaluation\EvaluationService;
use Doctrine\Common\Collections\ArrayCollection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Mockery\MockInterface;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 7/09/16
 * Time: 09:23
 */
class EvaluationControllerTest extends TestCase
{
    /** @var MockInterface */
    protected $groupRepo;
    /** @var MockInterface */
    protected $evaluationRepo;
    /** @var MockInterface */
    protected $branchRepo;
    /** @var MockInterface */
    protected $studentRepo;
    /** @var EvaluationService */
    protected $evaluationService;

    public function setUp()
    {
        parent::setUp();
        $this->groupRepo = $this->mock(App\Domain\Model\Identity\GroupRepository::class);
        $this->studentRepo = $this->mock(App\Domain\Model\Identity\StudentRepository::class);
        $this->branchRepo = $this->mock(App\Domain\Model\Education\BranchRepository::class);
        $this->evaluationRepo = $this->mock(App\Domain\Model\Evaluation\EvaluationRepository::class);

        $this->evaluationService = new EvaluationService($this->evaluationRepo, $this->branchRepo, $this->studentRepo);
    }

    /**
     * @test
     * @group EvaluationController
     */
    public function should_serialize_index()
    {
        //per group !
        $group = $this->makeGroup();
        $groupId = $group->getId()->toString();

        $evaluations = $this->makeEvaluations($group);

        $this->groupRepo->shouldReceive('get')
            ->once()
            ->andReturn($group);

        $this->evaluationRepo->shouldReceive('allEvaluationsForGroup')
            ->once()
            ->andReturn($evaluations);

        $this->get('/evaluations?group=' . $groupId)
            ->seeJsonStructure([
                '*' => [
                    'id',
                    'title',
                    'date',
                    'permanent',
                    'branchForGroup' => [
                        'branch' => [
                            'id',
                            'name',
                            'major' => [
                                'id',
                                'name'
                            ]
                        ]
                    ],
                    'max'
                ]
            ]);
    }

    /**
     * @test
     * @group EvaluationController
     */
    public function should_serialize_show()
    {
        $group = $this->makeGroup();
        $evaluation = $this->makeEvaluation($group);
        $evaluationId = $evaluation->getId()->toString();

        $this->evaluationRepo->shouldReceive('get')
            ->once()
            ->andReturn($evaluation);

        $this->get('/evaluations/' . $evaluationId)
            ->seeJson([
                'title' => $evaluation->getTitle(),
                'date' => $evaluation->getDate()->format('Y-m-d'),
                'permanent' => $evaluation->isPermanent(),
                'max' => $evaluation->getMax()
            ]);
    }

    /**
     * @test
     * @group EvaluationController
     */
    public function should_save_succes()
    {
        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $branch = $this->makeBranch();
        $group = $this->makeGroup();
        $branchForGroup = new BranchForGroup($branch, $group, ['start' => new DateTime], new EvaluationType(EvaluationType::POINT), 100);

        $title = $this->faker->word;
        $date = $this->faker->date;
        $max = $this->faker->biasedNumberBetween(10, 50);

        $data = [
            'title' => $title,
            'max' => $max,
            'branchForGroup' => [
                'id' => $branchForGroup->getId()->toString()
            ],
            'date' => $date,
            'results' => []
        ];

        /** @var Student $student */
        foreach ($students = $this->makeStudentCollection() as $student) {
            $data['results'][] = [
                'student' => [
                    'id' => $student->getId()->toString()
                ],
                'score' => $this->faker->biasedNumberBetween(0, $max),
                'redicodi' => $this->faker->randomElements(Redicodi::values())
            ];


            $this->studentRepo->shouldReceive('get')
                ->andReturnUsing(function ($id) use ($students) {
                    /** @var Student $ls */
                    foreach ($students as $ls) {
                        if ($ls->getId()->toString() == $id) {
                            return $ls;
                        }
                    }
                });

        }

        $this->branchRepo->shouldReceive('getBranchForGroup')
            ->once()
            ->andReturn($branchForGroup);

        $this->evaluationRepo->shouldReceive('insert')
            ->once()
            ->andReturn();

        $this->post('/evaluations', $data)
            ->seeJsonStructure([
                'id',
                'title',
                'max',
                'average',
                'median',
                'max',
                'branchForGroup',
                'results' => [
                    '*' => [
                        'student' => [
                            'id',
                            'displayName'
                        ],
                        'score',
                        'redicodi'
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @group EvaluationController
     */
    public function should_save_fail()
    {
        $message_bag = new MessageBag(['title is required.']);

        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => true, 'messages' => $message_bag]));

        $this->post('/evaluations', [])
            ->assertResponseStatus(422);
    }

    /**
     * @test
     * @group EvaluationController
     */
    public function should_update_success()
    {
        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => false]));

        $group = $this->makeGroup();

        $title = $this->faker->word;
        $date = $this->faker->date;
        $max = $this->faker->biasedNumberBetween(10, 50);

        $evaluation = $this->makeEvaluation($group);


        $data = [
            'id' => $evaluation->getId()->toString(),
            'title' => $title,
            'max' => $max,
            'branchForGroup' => [
                'id' => $evaluation->getBranchForGroup()->getId()->toString()
            ],
            'date' => $date,
            'results' => []
        ];

        /** @var PointResult $result */
        foreach ($evaluation->getResults() as $result) {
            $data['results'][] = [
                'student' => [
                    'id' => $result->getStudent()->getId()->toString()
                ],
                'score' => $this->faker->biasedNumberBetween(0, $max),
                'redicodi' => $this->faker->randomElements(Redicodi::values())
            ];

            $this->studentRepo->shouldReceive('get')
                ->andReturnUsing(function ($id) use ($evaluation) {
                    /** @var PointResult $lr */
                    foreach ($evaluation->getResults() as $lr) {
                        if ($lr->getStudent()->getId()->toString() == $id) {
                            return $lr->getStudent();
                        }
                    }
                });
        }


        $this->branchRepo->shouldReceive('getBranchForGroup')
            ->once()
            ->andReturn($evaluation->getBranchForGroup());

        $this->evaluationRepo->shouldReceive('get')
            ->once()
            ->andReturn($evaluation);

        $this->evaluationRepo->shouldReceive('update')
            ->once()
            ->andReturn();

        $this->put('/evaluations/' . $data['id'], $data)
            ->seeJsonStructure([
                'id',
                'title',
                'max',
                'average',
                'median',
                'max',
                'branchForGroup',
                'results' => [
                    '*' => []
                ]
            ])
            ->seeJson([
                'id' => $data['id'],
                'title' => $data['title']
            ]);
    }

    /**
     * @test
     * @group EvaluationController
     */
    public function should_fail_update()
    {
        $message_bag = new MessageBag(['title is required.']);

        Validator::shouldReceive('make')
            ->once()
            ->andReturn(Mockery::mock(['fails' => true, 'messages' => $message_bag]));

        $fakeId = \App\Domain\NtUid::generate(4);
        $this->put('/evaluations/' . $fakeId, [])
            ->assertResponseStatus(422);
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
