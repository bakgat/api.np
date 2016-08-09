<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/07/16
 * Time: 22:40
 */

namespace App\Http\Controllers\Education;


use App\Domain\Model\Education\BranchRepository;
use App\Domain\Model\Identity\GroupRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JMS\Serializer\SerializerInterface;
use Webpatser\Uuid\Uuid;

class BranchController extends Controller
{
    /** @var BranchRepository */
    private $branchRepo;

    private $groupRepo;

    public function __construct(BranchRepository $branchRepository, GroupRepository $groupRepository, SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->branchRepo = $branchRepository;
        $this->groupRepo = $groupRepository;
    }

    public function index(Request $request)
    {
        if ($request->has('group')) {
            $groupId = Uuid::import($request->get('group'));
        }

        $group = $this->groupRepo->get($groupId);
        if ($group) {
            return $this->response($this->branchRepo->all($group), ['major_list']);
        } else {
            return null;
        }
    }
}