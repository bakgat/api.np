<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 14/11/16
 * Time: 22:17
 */

namespace App\Http\Controllers\Events;


use App\Domain\Model\Evaluation\EvaluationRepository;
use App\Domain\Model\Events\EventTrackingRepository;
use App\Domain\Model\Identity\StaffRepository;
use App\Domain\Model\Identity\StudentRepository;
use App\Http\Controllers\Controller;
use DateTime;
use JMS\Serializer\SerializerInterface;

class EventController extends Controller
{
    /** @var EventTrackingRepository */
    private $eventRepo;
    /**
     * @var EvaluationRepository
     */
    private $evaluationRepo;
    /**
     * @var StudentRepository
     */
    private $studentRepo;
    /**
     * @var StaffRepository
     */
    private $staffRepo;

    public function __construct(EventTrackingRepository $eventTrackingRepository,
                                EvaluationRepository $evaluationRepository,
                                StudentRepository $studentRepository,
                                StaffRepository $staffRepository,
                                SerializerInterface $serializer)
    {
        $this->eventRepo = $eventTrackingRepository;
        parent::__construct($serializer);
        $this->evaluationRepo = $evaluationRepository;
        $this->studentRepo = $studentRepository;
        $this->staffRepo = $staffRepository;
    }

    public function reportEvents()
    {
        $logins = $this->eventRepo->allOfTypeAndAction('staff', 'login');
        $loginCount = 0;
        foreach ($logins as $login) {
            $loginCount += $login['action_count'];
        }

        $result = [
            'evaluations' => [
                'count' => $this->evaluationRepo->count(),
                'actions' => $this->eventRepo->allOfType('evaluation')
            ],
            'students' => [
                'count' => $this->studentRepo->count(),
            ],
            'staff' => [
                'count' => $this->staffRepo->count(),
            ],
            'logins' => [
                'count' => $loginCount,
                'actions' => $logins
            ],
            'redicodi' => $this->evaluationRepo->allRedicodiStats(new DateTime()),
        ];

        return $this->response($result, ['track_list']);
    }
}