<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 13/08/16
 * Time: 21:00
 */

namespace App\Domain\Model\Identity;


use App\Domain\NtUid;
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
     * Finds a staff member by its id, if not returns null.
     *
     * @param NtUid $id
     * @return Staff|null
     */
    public function find(NtUid $id);

    /**
     * Finds a staff member by its email-address, if not returns null.
     *
     * @param $email
     * @return Staff|null
     */
    public function findByEmail($email);

    /**
     * Gets an existing staff member by its id.
     *
     * @param NtUid $id
     * @return Staff
     */
    public function get(NtUid $id);

    /**
     * Saves a new staff member.
     *
     * @param Staff $staff
     * @return NtUid
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
     * @param NtUid $id
     * @return int Number of affected rows.
     */
    public function delete(NtUid $id);
}