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
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Webpatser\Uuid\Uuid;

class GroupController extends Controller
{
    /** @var GroupRepository */
    private $groupRepo;

    public function __construct(GroupRepository $groupRepository, SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->groupRepo = $groupRepository;
    }


    public function index()
    {
        return $this->response($this->groupRepo->all(), ['group']);
    }


    public function show($id)
    {
        if (!$id instanceof Uuid) {
            $id = Uuid::import($id);
        }
        return $this->response($this->groupRepo->find($id), ['group']);
    }

    public function allActiveStudents($id)
    {
        if (!$id instanceof Uuid) {
            $id = Uuid::import($id);
        }
        $activeStudents = $this->groupRepo->allActiveStudents($id);

        return $this->response($activeStudents, ['group_students']);
    }
}