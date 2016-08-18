<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 18/08/16
 * Time: 21:22
 */

namespace App\Http\Controllers\Identity;


use App\Domain\Model\Identity\RoleRepository;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    /** @var RoleRepository  */
    protected $roleRepo;

    public function __construct(RoleRepository $roleRepository, SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->roleRepo = $roleRepository;
    }

    public function index()
    {
        $result = $this->roleRepo->all();
        return $this->response($result, ['role_list']);
    }
}