<?php

namespace App\Domain\Services\Evaluation;

use App\Domain\Model\Education\BranchRepository;
use App\Domain\Model\Evaluation\Evaluation;
use App\Domain\Model\Evaluation\EvaluationRepository;
use App\Domain\Model\Evaluation\PointResult;
use App\Domain\Model\Identity\StudentRepository;
use App\Domain\NtUid;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 22/08/16
 * Time: 22:10
 */
class EvaluationService
{
    /** @var EvaluationRepository  */
    protected $evaluationRepo;
    /** @var BranchRepository */
    protected $branchRepo;
    /** @var StudentRepository  */
    protected $studentRepo;

    public function __construct(EvaluationRepository $evaluationRepository, BranchRepository $branchRepository, StudentRepository $studentRepository)
    {
        $this->evaluationRepo = $evaluationRepository;
        $this->branchRepo = $branchRepository;
        $this->studentRepo = $studentRepository;
    }

    public function create($data)
    {
        $title = $data['title'];
        $branchForGroupId = $data['branchForGroup']['id'];
        $branchForGroup = $this->branchRepo->getBranchForGroup($branchForGroupId);
        $date = convert_date_from_string($data['date']);
        $max = $data['max'];

        $results = $data['results'];

        //TODO: permanent or end evaluation
        //TODO: other types of evaluations ????
        $evaluation = new Evaluation($branchForGroup, $title, $date, $max);

        foreach ($results as $result) {
            $studentId = $result['student']['id'];
            $student = $this->studentRepo->get(NtUid::import($studentId));

            $score = $result['score'];
            $redicodi = $result['redicodi'];
            $pr = new PointResult($student, $score, $redicodi);

            $evaluation->addResult($pr);
        }

        $this->evaluationRepo->insert($evaluation);

        return $evaluation;
    }

    public function update($data)
    {
        $id = NtUid::import($data['id']);
        $title = $data['title'];
        $branchForGroupId = $data['branchForGroup']['id'];
        $branchForGroup = $this->branchRepo->getBranchForGroup($branchForGroupId);
        $date = convert_date_from_string($data['date']);
        $max = $data['max'];

        $results = $data['results'];

        /** @var Evaluation $evaluation */
        $evaluation = $this->evaluationRepo->get($id);

        $evaluation->update($title, $branchForGroup, $date, $max);

        foreach ($results as $result) {
            $studentId = $result['student']['id'];
            $student = $this->studentRepo->get(NtUid::import($studentId));

            $evaluation->updateResult($student, $result['score'], $result['redicodi']);
        }
        $this->evaluationRepo->update($evaluation);

        return $evaluation;

    }
}