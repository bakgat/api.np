<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 15/08/16
 * Time: 09:49
 */

namespace App\Http\Controllers\Identity;


use App\Domain\Model\Identity\StaffRepository;
use App\Domain\Services\Identity\StaffService;
use App\Domain\Uuid;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JMS\Serializer\SerializerInterface;

class StaffController extends Controller
{
    /** @var StaffRepository */
    protected $staffRepo;
    /** @var StaffService */
    protected $staffService;

    public function __construct(StaffRepository $staffRepository, StaffService $staffService, SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->staffRepo = $staffRepository;
        $this->staffService = $staffService;
    }

    public function index()
    {
        $result = $this->staffRepo->all();
        return $this->response($result, ['staff_list']);
    }

    public function show($id) {
        if(!$id instanceof Uuid) {
            $id = Uuid::import($id);
        }
        $member = $this->staffRepo->get($id);
        return $this->response($member, ['staff_detail']);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 422);
        }

        $staff = $this->staffService->create($request);
        return $this->response($staff, ['staff_detail']);

    }

}