<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 12/08/16
 * Time: 22:04
 */

namespace App\Http\Controllers\Identity;


use App\Domain\Model\Identity\StudentRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JMS\Serializer\SerializerInterface;

class UserController extends Controller
{
    /** @var StudentRepository */
    private $studentRepo;

    public function __construct(StudentRepository $studentRepository, SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->studentRepo = $studentRepository;
    }

    public function login(Request $request)
    {
        if ($request->has('email')) {
            if($request->get('email') == 'karl.vaniseghem@klimtoren.be') {
                return $this->response(['SUPERADMIN']);
            }
        }
    }
}