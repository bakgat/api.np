<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 15/08/16
 * Time: 09:49
 */

namespace App\Http\Controllers\Identity;


use App\Domain\Model\Identity\StaffRepository;
use App\Domain\Uuid;
use App\Http\Controllers\Controller;
use JMS\Serializer\SerializerInterface;

class StaffController extends Controller
{
    /** @var StaffRepository */
    protected $staffRepo;

    public function __construct(StaffRepository $staffRepository, SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->staffRepo = $staffRepository;
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
}