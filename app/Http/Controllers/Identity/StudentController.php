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
use Illuminate\Support\Collection;
use Webpatser\Uuid\Uuid;

class StudentController extends Controller
{
    /** @var StudentRepository studentRepo */
    private $studentRepo;

    public function __construct(StudentRepository $studentRepo)
    {
        $this->studentRepo = $studentRepo;
    }

    public function index()
    {
        return Collection::make($this->studentRepo->all());
    }

    public function show($id)
    {
        if (!$id instanceof Uuid) {
            $id = Uuid::import($id);
        }
        return Collection::make($this->studentRepo->find($id));
    }

    public function allGroups($id) {
        if (!$id instanceof Uuid) {
            $id = Uuid::import($id);
        }
        return $this->studentRepo->allGroups($id);
    }
}