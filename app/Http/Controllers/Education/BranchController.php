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
use Webpatser\Uuid\Uuid;

class BranchController extends Controller
{
    /** @var BranchRepository */
    private $branchRepo;

    private $groupRepo;

    public function __construct(BranchRepository $branchRepository, GroupRepository $groupRepository)
    {
        $this->branchRepo = $branchRepository;
        $this->groupRepo = $groupRepository;
    }

    public function index($groupId)
    {
        if (!$groupId instanceof Uuid) {
            $groupId = Uuid::import($groupId);
        }
        $group = $this->groupRepo->get($groupId);
        return $this->branchRepo->all($group);
    }
}