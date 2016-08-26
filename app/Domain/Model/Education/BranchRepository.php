<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 18/07/16
 * Time: 10:06
 */

namespace App\Domain\Model\Education;


use App\Domain\Model\Identity\Group;
use Webpatser\Uuid\Uuid;

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
     * @param Uuid $id
     * @return Branch|null
     */
    public function findBranch(Uuid $id);

    /**
     * Gets an existing Branch by its id.
     *
     * @param Uuid $id
     * @return Branch
     */
    public function getBranch(Uuid $id);

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
     * @return ArrayColleciton
     */
    public function allBranchesInGroup(Group $group);

    /**
     * Gets all the active major in a group.
     *
     * @param Group $group
     * @return ArrayCollection|Major[]
     */
    public function allMajors(Group $group);

    /**
     * Finds a major by its id, if not returns null.
     *
     * @param Uuid $id
     * @return Major|null
     */
    public function findMajor(Uuid $id);

    /**
     * Gets an existing major by its id.
     *
     * @param Uuid $id
     * @return Major
     */
    public function getMajor(Uuid $id);

    /**
     * Saves a new Major
     *
     * @param Major $major
     * @return Uuid
     */
    public function insertMajor(Major $major);
    /**
     * Saves a new Branch
     *
     * @param Branch $branch
     * @return Uuid
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
     * @param Uuid $id
     * @return int Number of affected rows.
     */
    public function deleteBranch(Uuid $id);

    /**
     * Deletes an existing Major.
     *
     * @param Uuid $id
     * @return int Number of affected rows.
     */
    public function deleteMajor(Uuid $id);

    /**
     * @param $branchForGroupId
     * @return BranchForGroup
     */
    public function getBranchForGroup($branchForGroupId);
}