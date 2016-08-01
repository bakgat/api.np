<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 20/06/16
 * Time: 11:29
 */

namespace App\Http\Controllers\Identity;


use App\Domain\Model\Identity\StudentRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JMS\Serializer\SerializerInterface;
use Webpatser\Uuid\Uuid;

class StudentController extends Controller
{
    /** @var StudentRepository studentRepo */
    private $studentRepo;

    public function __construct(StudentRepository $studentRepo, SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->studentRepo = $studentRepo;
    }

    public function index(Request $request)
    {
        if ($request->has('flat')) {
            $field = $request->get('flat');
            return $this->response($this->studentRepo->flat($field));
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
        return $this->response($this->studentRepo->allGroups($id), ['list']);
    }
}