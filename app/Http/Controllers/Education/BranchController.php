<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 19/07/16
 * Time: 22:40
 */

namespace App\Http\Controllers\Education;


use App\Domain\Model\Education\BranchRepository;
use App\Domain\Model\Evaluation\EvaluationType;
use App\Domain\Model\Identity\GroupRepository;
use App\Domain\NtUid;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JMS\Serializer\SerializerInterface;

class BranchController extends Controller
{
    /** @var BranchRepository */
    private $branchRepo;
    /** @var GroupRepository */
    private $groupRepo;

    public function __construct(BranchRepository $branchRepository, GroupRepository $groupRepository, SerializerInterface $serializer)
    {
        parent::__construct($serializer);
        $this->branchRepo = $branchRepository;
        $this->groupRepo = $groupRepository;
    }

    public function index(Request $request)
    {
        /* HAS GROUP */
        if ($request->has('group')) {
            $groupId = NtUid::import($request->get('group'));
        } else {
            return response('Group must be given.', 500);
        }
        $group = $this->groupRepo->get($groupId);
        return $this->response($this->branchRepo->all($group), ['major_list']);
    }

    public function indexMajors(Request $request) {
        $majors = $this->branchRepo->allMajors();
        return $this->response($majors, ['major_list']);
    }

    
}