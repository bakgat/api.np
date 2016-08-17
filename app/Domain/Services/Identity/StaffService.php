<?php

namespace App\Domain\Services\Identity;
use App\Domain\Model\Identity\Gender;
use App\Domain\Model\Identity\Staff;
use App\Domain\Model\Identity\StaffRepository;
use App\Domain\Uuid;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 17/08/16
 * Time: 22:17
 */
class StaffService
{
    /** @var  StaffRepository */
    protected $staffRepo;
    public function __construct(StaffRepository $staffRepository)
    {
        $this->staffRepo = $staffRepository;
    }

    public function all() {
        return $this->staffRepo->all();
    }

    public function get($id) {
        if(!$id instanceof Uuid) {
            $id = Uuid::import($id);
        }
        $member = $this->staffRepo->get($id);
        return $member;
    }

    public function create(Request $request) {

        $firstName = $request->get('firstName');
        $lastName = $request->get('lastName');
        $email = $request->get('email');
        $birthday = null;
        if($request->has('birthday')) {
            $birthday = strtotime($request->get('birthday'));
            if($birthday!==false) {
                $birthday = new DateTime(date('Y-m-d', $birthday));
            }
        }

        $gender = new Gender($request->get('gender'));

        $staff = new Staff($firstName, $lastName, $email, $gender, $birthday);
        $this->staffRepo->insert($staff);

        return $staff;
    }

    public function update(Request $request) {
        $id = Uuid::import($request->get('id'));
        $staff = $this->get($id);

        $firstName = $request->get('firstName');
        $lastName = $request->get('lastName');
        $email = $request->get('email');
        $birthday = null;
        if($request->has('birthday')) {
            $birthday = strtotime($request->get('birthday'));
            if($birthday!==false) {
                $birthday = new DateTime(date('Y-m-d', $birthday));
            }
        }

        $gender = new Gender($request->get('gender'));

    }
}