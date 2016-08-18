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
    /** @var StaffService */
    protected $staffService;

    public function __construct(StaffService $staffService, SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->staffService = $staffService;
    }

    public function index()
    {
        $result = $this->staffService->all();
        return $this->response($result, ['staff_list']);
    }

    public function show($id)
    {
        $member = $this->staffService->get($id);
        return $this->response($member, ['staff_detail']);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email',
            'gender' => 'required'
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 422);
        }

        $staff = $this->staffService->create($request->all());
        return $this->response($staff, ['staff_detail']);

    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email',
            'gender' => 'required'
        ]);

        if ($validator->fails()) {
            return response($validator->messages(), 422);
        }

        $staff = $this->staffService->update($request->all());
        return $this->response($staff, ['staff_detail']);
    }

    public function addGroup($id, $groupId)
    {
        $this->staffService->addToGroup($id, $groupId);
    }

    public function removeGroup($id, $groupId)
    {
        $this->staffService->removeFromGropu($id, $groupId);
    }

}