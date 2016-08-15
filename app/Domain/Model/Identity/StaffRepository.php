<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 13/08/16
 * Time: 21:00
 */

namespace App\Domain\Model\Identity;


use App\Domain\Uuid;
use Doctrine\Common\Collections\ArrayCollection;

interface StaffRepository
{
    /**
     * Get all staff members.
     *
     * @return ArrayCollection
     */
    public function all();

    /**
     * Get all active staff members in a role.
     *
     * @param Role $group
     * @param DateTime|null $date
     * @return ArrayCollection
     */
    public function allActiveInRole(Role $group, $date=null);

    /**
     * Gets all the active staff members in a group.
     *
     * @param Group $group
     * @param DateTime|null $date
     * @return Collection
     */
    public function allActiveInGroup(Group $group, $date=null);

    /**
     * Finds a staff member by its id, if not returns null.
     *
     * @param Uuid $id
     * @return Staff|null
     */
    public function find(Uuid $id);

    /**
     * Gets an existing staff member by its id.
     *
     * @param Uuid $id
     * @return Student
     */
    public function get(Uuid $id);

    /**
     * Saves a new staff member.
     *
     * @param Staff $staff
     * @return Uuid
     */
    public function insert(Staff $staff);

    /**
     * Saves an existing staff member.
     *
     * @param Staff $staff
     * @return int Number of affected rows.
     */
    public function update(Staff $staff);

    /**
     * Deletes an existing staff member.
     *
     * @param Uuid $id
     * @return int Number of affected rows.
     */
    public function delete(Uuid $id);
}