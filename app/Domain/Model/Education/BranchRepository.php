<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 18/07/16
 * Time: 10:06
 */

namespace App\Domain\Model\Education;


use App\Domain\Model\Identity\Group;
use App\Domain\NtUid;
use Doctrine\Common\Collections\ArrayCollection;

interface BranchRepository
{

    /**
     * Gets all active majors and branches for a group.
     *
     * @param Group $group
     * @return ArrayCollection
     */
    public function all(Group $group);

    /**
     * Finds a branch by its id, if not returns null.
     * @param NtUid $id
     * @return Branch|null
     */
    public function findBranch(NtUid $id);

    /**
     * Gets an existing Branch by its id.
     *
     * @param NtUid $id
     * @return Branch
     */
    public function getBranch(NtUid $id);

    /**
     * Gets all active branches of a major for a group.
     *
     * @param Group $group
     * @param Major $major
     * @return ArrayCollection|Branch[]
     */
    public function allBranches(Group $group, Major $major);

    /**
     * @param Group $group
     * @return ArrayCollection
     */
    public function allBranchesInGroup(Group $group);


    /**
     * Finds a major by its id, if not returns null.
     *
     * @param NtUid $id
     * @return Major|null
     */
    public function findMajor(NtUid $id);

    /**
     * Gets an existing major by its id.
     *
     * @param NtUid $id
     * @return Major
     */
    public function getMajor(NtUid $id);

    /**
     * Saves a new Major
     *
     * @param Major $major
     * @return NtUid
     */
    public function insertMajor(Major $major);
    /**
     * Saves a new Branch
     *
     * @param Branch $branch
     * @return NtUid
     */
    public function insertBranch(Branch $branch);

    /**
     * Saves an existing major.
     *
     * @param Major $major
     * @return int Number of affected rows.
     */
    public function update(Major $major);

    /**
     * Deletes an existing Branch.
     *
     * @param NtUid $id
     * @return int Number of affected rows.
     */
    public function deleteBranch(NtUid $id);

    /**
     * Deletes an existing Major.
     *
     * @param NtUid $id
     * @return int Number of affected rows.
     */
    public function deleteMajor(NtUid $id);

    /**
     * @param NtUid $branchForGroupId
     * @return BranchForGroup
     */
    public function getBranchForGroup(NtUid $branchForGroupId);
}