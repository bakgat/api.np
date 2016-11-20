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
        $logins = $this->eventRepo->allOfType('login');
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
                'count' => count($logins),
                'actions' => $logins
            ],
        ];

        return $this->response($result, ['track_list']);
    }
}