<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 20/07/16
 * Time: 22:40
 */

namespace App\Http\Controllers\Identity;


use App\Domain\Model\Identity\GroupRepository;
use App\Http\Controllers\Controller;
use Webpatser\Uuid\Uuid;

class GroupController extends Controller
{
    /** @var GroupRepository */
    private $groupRepo;

    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepo = $groupRepository;
    }


    public function index()
    {
        return $this->groupRepo->allActive();
    }


    public function show($id)
    {
        if (!$id instanceof Uuid) {
            $id = Uuid::import($id);
        }
        return $this->groupRepo->find($id);
    }

    public function allActiveStudents($id)
    {
        if (!$id instanceof Uuid) {
            $id = Uuid::import($id);
        }
        return $this->groupRepo->allActiveStudents($id);
    }
}