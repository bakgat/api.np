<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 7/11/16
 * Time: 13:24
 */

namespace App\Http\Controllers\Evaluation;


use App\Domain\Model\Education\BranchRepository;
use App\Domain\Model\Evaluation\IACRepository;
use App\Domain\Model\Identity\StudentRepository;
use App\Domain\Model\Time\DateRange;
use App\Domain\NtUid;
use App\Domain\Services\Evaluation\IacService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JMS\Serializer\SerializerInterface;

class IacController extends Controller
{
    /** @var IACRepository */
    private $iacRepo;
    /** @var BranchRepository */
    private $branchRepo;
    /** @var StudentRepository */
    private $studentRepo;
    /** @var IacService */
    private $iacService;

    public function __construct(IACRepository $iacRepository, BranchRepository $branchRepository,
                                StudentRepository $studentRepository, IacService $iacService, SerializerInterface $serializer)
    {
        $this->iacRepo = $iacRepository;
        $this->branchRepo = $branchRepository;
        $this->studentRepo = $studentRepository;
        $this->iacService = $iacService;
        parent::__construct($serializer);
    }

    public function indexGoals()
    {
        $goals = $this->iacRepo->allGoals();
        return $this->response($goals, ['iac_goals']);
    }

    public function indexGoalsByMajor($majorId)
    {
        $majorId = NtUid::import($majorId);
        $major = $this->branchRepo->getMajor($majorId);
        $goals = $this->iacRepo->allGoalsForMajor($major);
        return $this->response($goals, ['iac_goals']);
    }

    /**
     * @param $branchId
     * @return string
     */
    public function indexGoalsByBranch($branchId)
    {
        $branchId = NtUid::import($branchId);
        $branch = $this->branchRepo->getBranch($branchId);
        $goals = $this->iacRepo->allGoalsForBranch($branch);
        return $this->response($goals, ['iac_goals']);
    }

    public function indexGoalsForStudent(Request $request, $studentId)
    {
        if($request->has('start')) {
            $start = $request->get('start');
            $end = $request->get('end');
        } else {
            $start = '2016-09-01';
            $end = '2016-12-31';
        }
        $range = DateRange::fromData(['start' => $start, 'end' => $end]);

        $iac = $this->iacService->getIACsForStudent($studentId, $range);
        return $this->response($iac, ['student_iac']);
    }
}