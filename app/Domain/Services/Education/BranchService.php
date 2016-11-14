<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 12/11/16
 * Time: 20:34
 */

namespace App\Domain\Services\Education;


use App\Domain\Model\Education\BranchRepository;
use App\Domain\NtUid;

class BranchService
{
    /**
     * @var BranchRepository
     */
    private $branchRepo;

    public function __construct(BranchRepository $branchRepository)
    {
        $this->branchRepo = $branchRepository;
    }

    public function getBranch(NtUid $id)
    {
        return $this->branchRepo->getBranch($id);
    }
}