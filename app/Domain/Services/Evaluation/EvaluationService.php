<?php

namespace App\Domain\Services\Evaluation;

use App\Domain\Model\Education\BranchRepository;
use App\Domain\Model\Evaluation\ComprehensiveResult;
use App\Domain\Model\Evaluation\Evaluation;
use App\Domain\Model\Evaluation\EvaluationRepository;
use App\Domain\Model\Evaluation\EvaluationType;
use App\Domain\Model\Evaluation\MultiplechoiceResult;
use App\Domain\Model\Evaluation\PointResult;
use App\Domain\Model\Evaluation\SpokenResult;
use App\Domain\Model\Events\EventTracking;
use App\Domain\Model\Events\EventTrackingRepository;
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
    /** @var EvaluationRepository */
    protected $evaluationRepo;
    /** @var BranchRepository */
    protected $branchRepo;
    /** @var StudentRepository */
    protected $studentRepo;
    /** @var EventTrackingRepository */
    protected $trackRepo;

    public function __construct(EvaluationRepository $evaluationRepository,
                                EventTrackingRepository $eventTrackingRepository,
                                BranchRepository $branchRepository,
                                StudentRepository $studentRepository)
    {
        $this->evaluationRepo = $evaluationRepository;
        $this->branchRepo = $branchRepository;
        $this->studentRepo = $studentRepository;
        $this->trackRepo = $eventTrackingRepository;
    }

    public function create($data)
    {
        $title = $data['title'];
        $date = convert_date_from_string($data['date']);

        $branchForGroupId = $data['branchForGroup']['id'];
        $branchForGroup = $this->branchRepo->getBranchForGroup(NtUid::import($branchForGroupId));
        $permanent = $data['permanent'];
        $final = $data['final'];

        $type = $branchForGroup->getEvaluationType();

        //TODO max must be set type is point
        $max = isset($data['max']) ? $data['max'] : null;


        $evaluation = new Evaluation($branchForGroup, $title, $date, $max, $permanent, $final);

        //HANDLE EACH KIND OF EVALUATION RESULTS
        if ($type->getValue() == EvaluationType::POINT) {
            $results = $data['pointResults'];
            foreach ($results as $result) {
                $studentId = $result['student']['id'];
                $student = $this->studentRepo->get(NtUid::import($studentId));

                $score = $result['score'];
                $redicodi = $result['redicodi'];
                $pr = new PointResult($student, $score, $redicodi);

                $evaluation->addPointResult($pr);
            }
        } else if ($type->getValue() == EvaluationType::COMPREHENSIVE) {
            $results = $data['comprehensiveResults'];
            foreach ($results as $result) {
                $studentId = $result['student']['id'];
                $student = $this->studentRepo->get(NtUid::import($studentId));

                $cr = new ComprehensiveResult($student);
                $evaluation->addComprehensiveResult($cr);
            }
        } else if ($type->getValue() == EvaluationType::SPOKEN) {
            $results = $data['spokenResults'];
            foreach ($results as $result) {
                $studentId = $result['student']['id'];
                $student = $this->studentRepo->get(NtUid::import($studentId));

                $summary = isset($result['summary']) ? $result['summary'] : null;
                $sr = new SpokenResult($student, $summary);
                $evaluation->addSpokenResult($sr);
            }
        } else if ($type->getValue() == EvaluationType::MULTIPLECHOICE) {
            $results = $data['multiplechoiceResults'];
            $evaluation->setSettings($data['settings']);

            foreach ($results as $result) {
                $studentId = $result['student']['id'];
                $student = $this->studentRepo->get(NtUid::import($studentId));

                $selected = isset($result['selected']) ? $result['selected'] : null;
                $mcr = new MultiplechoiceResult($student, $selected);
                $evaluation->addMultiplechoiceResult($mcr);
            }
        }


        $this->evaluationRepo->insert($evaluation);

        $userId = $data['auth_token'];
        $track = new EventTracking('staff', $userId, 'evaluation', 'insert', $evaluation->getId());
        $this->trackRepo->save($track);

        return $evaluation;
    }

    public function update($data)
    {
        $id = NtUid::import($data['id']);
        $title = $data['title'];
        $branchForGroupId = $data['branchForGroup']['id'];
        $branchForGroup = $this->branchRepo->getBranchForGroup(NtUid::import($branchForGroupId));
        $date = convert_date_from_string($data['date']);
        $max = isset($data['max']) ? $data['max'] : null;
        $permanent = $data['permanent'];
        $final = $data['final'];

        /** @var Evaluation $evaluation */
        $evaluation = $this->evaluationRepo->get($id);

        $evaluation->update($title, $branchForGroup, $date, $max, $permanent, $final);

        $type = $branchForGroup->getEvaluationType();
        //TODO HOW TO UPDATE FOR INSERT / DELETE OTHER STUDENTS
        if ($type->getValue() == EvaluationType::POINT) {
            $results = $data['pointResults'];
            foreach ($results as $result) {
                $studentId = $result['student']['id'];
                $student = $this->studentRepo->get(NtUid::import($studentId));

                $evaluation->updatePointResult($student, $result['score'], $result['redicodi']);
            }
        }
        $this->evaluationRepo->update($evaluation);

        $userId = NtUid::import($data['auth_token']);
        $track = new EventTracking('staff', $userId, 'evaluation', 'update', $evaluation->getId());
        $this->trackRepo->save($track);

        return $evaluation;

    }
}