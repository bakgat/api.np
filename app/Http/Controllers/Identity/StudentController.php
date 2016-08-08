<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 20/06/16
 * Time: 11:29
 */

namespace App\Http\Controllers\Identity;


use App\Domain\Model\Identity\GroupRepository;
use App\Domain\Model\Identity\StudentRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JMS\Serializer\SerializerInterface;
use Webpatser\Uuid\Uuid;

class StudentController extends Controller
{
    /** @var StudentRepository studentRepo */
    private $studentRepo;
    /** @var GroupRepository */
    private $groupRepo;

    public function __construct(StudentRepository $studentRepo, GroupRepository $groupRepository, SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->studentRepo = $studentRepo;
        $this->groupRepo = $groupRepository;
    }

    public function index(Request $request)
    {
        if ($request->has('flat')) {
            $field = $request->get('flat');
            return $this->response($this->studentRepo->flat($field));
        }
        if ($request->has('group')) {
            $groupId = Uuid::import($request->get('group'));
            $group = $this->groupRepo->get($groupId);
            return $this->response($this->studentRepo->allActiveInGroup($group), ['student_list']);
        }

        return $this->response($this->studentRepo->all(), ['student_list']);
    }

    public function show($id)
    {
        if (!$id instanceof Uuid) {
            $id = Uuid::import($id);
        }
        return $this->response($this->studentRepo->find($id), ['student_detail']);
    }

    public function allGroups($id)
    {
        if (!$id instanceof Uuid) {
            $id = Uuid::import($id);
        }
        return $this->response($this->studentRepo->allGroups($id), ['student_groups']);
    }

    public function allRedicodi($id)
    {
        if (!$id instanceof Uuid) {
            $id = Uuid::import($id);
        }
        return $this->response($this->studentRepo->allRedicodi($id), ['student_redicodi']);
    }
}