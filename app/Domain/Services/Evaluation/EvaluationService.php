<?php

namespace App\Domain\Services\Evaluation;

use App\Domain\Model\Education\BranchForGroup;
use App\Domain\Model\Education\BranchRepository;
use App\Domain\Model\Evaluation\ComprehensiveResult;
use App\Domain\Model\Evaluation\Evaluation;
use App\Domain\Model\Evaluation\EvaluationRepository;
use App\Domain\Model\Evaluation\EvaluationType;
use App\Domain\Model\Evaluation\FeedbackResult;
use App\Domain\Model\Evaluation\GraphRange;
use App\Domain\Model\Evaluation\MultiplechoiceResult;
use App\Domain\Model\Evaluation\PointResult;
use App\Domain\Model\Evaluation\RR;
use App\Domain\Model\Evaluation\SpokenResult;
use App\Domain\Model\Events\EventTracking;
use App\Domain\Model\Events\EventTrackingRepository;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Identity\StudentRepository;
use App\Domain\NtUid;
use DateTime;
use Doctrine\Common\Collections\Criteria;

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
    /** @var GraphRangeService */
    protected $graphRangeService;

    public function __construct(EvaluationRepository $evaluationRepository,
                                EventTrackingRepository $eventTrackingRepository,
                                BranchRepository $branchRepository,
                                StudentRepository $studentRepository,
                                GraphRangeService $graphRangeService)
    {
        $this->evaluationRepo = $evaluationRepository;
        $this->branchRepo = $branchRepository;
        $this->studentRepo = $studentRepository;
        $this->trackRepo = $eventTrackingRepository;
        $this->graphRangeService = $graphRangeService;
    }

    public function get(NtUid $id)
    {
        return $this->evaluationRepo->get($id);
    }

    public function create($data)
    {
        $title = $data['title'];
        $date = convert_date_from_string($data['date']);

        $branchForGroupId = $data['branchForGroup']['id'];
        $branchForGroup = $this->branchRepo->getBranchForGroup(NtUid::import($branchForGroupId));
        $permanent = $data['permanent'];


        $type = $branchForGroup->getEvaluationType();

        //TODO max must be set type is point
        $max = isset($data['max']) ? $data['max'] : null;


        $evaluation = new Evaluation($branchForGroup, $title, $date, $max, $permanent);

        //HANDLE EACH KIND OF EVALUATION RESULTS
        if ($type->getValue() == EvaluationType::POINT) {


            $results = $data['pointResults'];
            foreach ($results as $result) {
                if (!is_null($result['score'])) {
                    $studentId = $result['student']['id'];
                    $student = $this->studentRepo->get(NtUid::import($studentId));

                    $score = $result['score'];
                    $redicodi = $result['redicodi'];
                    $pr = new PointResult($student, $score, $redicodi);

                    $evaluation->addPointResult($pr);
                }
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
        } else if ($type->getValue() == EvaluationType::FEEDBACK) {
            $results = $data['feedbackResults'];
            foreach ($results as $result) {
                $studentId = $result['student']['id'];
                $student = $this->studentRepo->get(NtUid::import($studentId));

                $summary = isset($result['summary']) ? $result['summary'] : null;
                $fr = new FeedbackResult($student, $summary);
                $evaluation->addFeedbackResult($fr);
            }
        }


        $this->evaluationRepo->insert($evaluation);

        /*
         * Only need graph_ranges for point results.
         * After adding all points recalculate totals for each
         * matching graph range AND branchForGroup
         */
        if ($type->getValue() == EvaluationType::POINT) {
            $this->sanitizeTotals($date, $branchForGroup);
        }

        $userId = $data['auth_token'];
        $track = new EventTracking('staff', $userId, 'evaluation', 'insert', $evaluation->getId());
        $this->trackRepo->save($track);

        return $evaluation;
    }

    public function sanitizeAll() {
        $evaluations = $this->evaluationRepo->allEvaluations();
        /** @var Evaluation $evaluation */
        foreach ($evaluations as $evaluation) {
            $this->sanitizeTotals($evaluation->getDate(), $evaluation->getBranchForGroup());
        }
    }

    public function sanitizeTotals(DateTime $date, BranchForGroup $branchForGroup)
    {
        $grs = $this->graphRangeService->find($date, $branchForGroup->getGroup()->getId());
        /** @var GraphRange $gr */
        foreach ($grs as $gr) {
            $prs = $this->evaluationRepo->allPointResults($gr, $branchForGroup);
            $rrs = $this->evaluationRepo->allRangeResults($gr, $branchForGroup);
            /** @var RR $rs */
            foreach ($rrs as $rs) {
                $rs->setEvaluationCount(0); //reset
                $rs->setRedicodi('');
            }

            foreach ($prs as $pr) {
                $found = false;

                /** @var RR $foundRR */
                $foundRR = array_first($rrs, function ($rr) use ($pr, $rrs) {
                    return $rrs[$rr]->getStudent()->getId() == $pr['student_id'];
                });
                $id=null;

                if (!$foundRR) {
                    $foundRR = new RR();
                    $foundRR->setId(NtUid::generate(4));
                    $student = $this->studentRepo->get(NtUid::import($pr['student_id']));
                    $foundRR->setBranchForGroup($branchForGroup);
                    $foundRR->setGraphRange($gr);
                    $foundRR->setStudent($student);
                    $foundRR->setEvaluationCount(0); //init
                    $foundRR->setRedicodi(''); //init
                } else {
                    $found = true;
                }
                if($pr['permanent']) {
                    $foundRR->setPermanentRaw($pr['raw_score']);
                } else {
                    $foundRR->setEndRaw($pr['raw_score']);
                }
                $foundRR->setMax($pr['max']);

                //calculate end total and permanent total
                $eTotal = 0;
                $pTotal = 0;
                if($foundRR->getPermanentRaw() && $foundRR->getEndRaw()) {
                    $eTotal = ($foundRR->getEndRaw() / $foundRR->getMax()) * 60;
                    $pTotal = ($foundRR->getPermanentRaw() / $foundRR->getMax()) * 40;

                } else if (!$foundRR->getPermanentRaw()) {
                    $eTotal = ($foundRR->getEndRaw() / $foundRR->getMax()) * 100;
                } else {
                    $pTotal = ($foundRR->getPermanentRaw() / $foundRR->getMax()) * 100;
                }
                $foundRR->setTotal((($eTotal + $pTotal) / 100) * $foundRR->getMax());

                if(!$found) {
                    array_push($rrs, $foundRR);
                }
                $evCount = $pr['ev_count'] + $foundRR->getEvaluationCount(); //can be from permanent or end counter
                $foundRR->setEvaluationCount($evCount);

                $foundRedicodi = explode(',', $foundRR->getRedicodi());
                $evRedicodi = explode(',', $pr['redicodi']);
                $redicodi = array_merge(array_filter($foundRedicodi), array_filter($evRedicodi)); //trim and merge
                $foundRR->setRedicodi(implode(',', array_unique($redicodi))); //unique values

                $this->evaluationRepo->updateOrCreateRR($foundRR);
            }

        }
        echo 'done';
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

        /** @var Evaluation $evaluation */
        $evaluation = $this->evaluationRepo->get($id);

        $evaluation->update($title, $branchForGroup, $date, $max, $permanent);

        if (isset($data['settings'])) {
            $evaluation->setSettings($data['settings']);
        }

        $type = $branchForGroup->getEvaluationType();

        //TODO fine tune this => lots of selects !!!
        //Can this be combined into one ?
        //as in select * from students where id IN(a, b, c)
        if ($type->getValue() == EvaluationType::POINT) {


            $results = $data['pointResults'];

            //DELETED
            $studIds = array_map(function ($result) {
                return $result['student']['id'];
            }, $results);
            /** @var PointResult $pointResult */
            foreach ($evaluation->getPointResults() as $pointResult) {
                if (!in_array($pointResult->getStudent()->getId()->toString(), $studIds)) {
                    $evaluation->removePointResult($pointResult);
                }
            }


            //ADDED OR UPDATED
            foreach ($results as $result) {
                if (!is_null($result['score'])) {
                    //@todo: less selects when updatePointResults(studentID !!, score, redicodi);
                    $studentId = $result['student']['id'];
                    $student = $this->studentRepo->get(NtUid::import($studentId));

                    $evaluation->updatePointResult($student, $result['score'], $result['redicodi']);
                }
            }


        } else if ($type->getValue() == EvaluationType::MULTIPLECHOICE) {
            $results = $data['multiplechoiceResults'];
            foreach ($results as $result) {
                $studentId = $result['student']['id'];
                $student = $this->studentRepo->get(NtUid::import($studentId));

                $evaluation->updateMultiplechoiceResult($student, $result['selected']);
            }
        } else if ($type->getValue() == EvaluationType::FEEDBACK) {
            $results = $data['feedbackResults'];
            foreach ($results as $result) {
                $studentId = $result['student']['id'];
                $student = $this->studentRepo->get(NtUid::import($studentId));

                $evaluation->updateFeedbackResult($student, $result['summary']);
            }
        }

        $this->evaluationRepo->update($evaluation);

        /*
         * Only need graph_ranges for point results.
         * After adding all points recalculate totals for each
         * matching graph range AND branchForGroup
         */
        if ($type->getValue() == EvaluationType::POINT) {
            $this->sanitizeTotals($date, $branchForGroup);
        }

        $userId = NtUid::import($data['auth_token']);
        $track = new EventTracking('staff', $userId, 'evaluation', 'update', $evaluation->getId());
        $this->trackRepo->save($track);

        return $evaluation;

    }

    /**
     * @param $id
     * @return bool
     */
    public function delete($id)
    {
        $evaluation = $this->get(NtUid::import($id));
        return $this->evaluationRepo->remove($evaluation);
    }

    public function calculateTotals($graphRangeId, $branchForGroupId)
    {
        /*
         * STEPS
         * ----------------------
         * 1. get RR for branchForGroup and graphRange
         * 2. get PRs (group by student, sum(score), sum(max), concat(redicodi) for all evaluations in branchForGroup and graphRange
         * 3. Loop through PRs per student.
         * 4. If student_id in RR => update
         * 5. Else create RR
         * 6. Flush results to RR
         *
         */
    }

    public function getRangeResults($graphRangeId, $branchForGroupId)
    {
        return $this->evaluationRepo->allRangeResults($graphRangeId, $branchForGroupId);
    }

    public function allPointResultsForBranchForGroupBetween($range, $branchForGroup)
    {
        return $this->evaluationRepo->allPointResults($range, $branchForGroup);
    }
}