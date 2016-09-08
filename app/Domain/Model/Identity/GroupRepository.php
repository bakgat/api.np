<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 23/06/16
 * Time: 15:25
 */

namespace App\Domain\Model\Identity;


use App\Domain\NtUid;
use Doctrine\Common\Collections\ArrayCollection;

interface GroupRepository
{
    /**
     * Gets all the groups.
     *
     * @return ArrayCollection|Group[]
     */
    public function all();


    /**
     * Gets all the active groups
     *
     * @return ArrayCollection|Group[]
     */
    public function allActive();

    /**
     * Finds a group by its id, if not returns null.
     *
     * @param NtUid $id
     * @return Group|null
     */
    public function find(NtUid $id);

    /**
     * Gets an existing group by its id.
     *
     * @param NtUid $id
     * @return Group
     */
    public function get(NtUid $id);

    /**
     * Saves a new group.
     *
     * Note: the name of the group must be unique.
     *
     * @param Group $group
     * @return NtUid
     */
    public function insert(Group $group);

    /**
     * Saves an existing group.
     *
     * @param Group $group
     * @return int Number of affected rows.
     */
    public function update(Group $group);

    /**
     * Deletes an existing group.
     *
     * @param $id
     * @return int Number of affected rows.
     */
    public function delete(NtUid $id);

    /**
     * Gets all the active students in a group.
     *
     * @param NtUid $id
     * @return ArrayCollection|Students[]
     */
    public function allActiveStudents(NtUid $id);

    /**
     * @param NtUid $id
     * @return StaffInGroup
     */
    public function getStaffGroup(NtUid $id);

    /**
     * @param StaffInGroup $staffGroup
     * @return int Number of affected rows
     */
    public function updateStaffGroup(StaffInGroup $staffGroup);

    /**
     * @param NtUid $id
     * @return StudentInGroup
     */
    public function getStudentGroup(NtUid $id);

    /**
     * @param $studentGroup
     * @return StudentInGroup
     */
    public function updateStudentGroup($studentGroup);
}