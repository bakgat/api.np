<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 20/06/16
 * Time: 11:29
 */

namespace App\Http\Controllers\Person;


use App\Http\Controllers\Controller;

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
        return $this->studentRepo->all();
    }

    public function show($id)
    {
        return $this->studentRepo->find($id);
    }
}