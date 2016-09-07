<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 13/08/16
 * Time: 20:58
 */

namespace App\Domain\Model\Identity;


use App\Domain\NtUid;
use Doctrine\Common\Collections\ArrayCollection;

interface RoleRepository
{
    /**
     * Gets all the roles available.
     *
     * @return ArrayCollection
     */
    public function all();

    /**
     * Gets an existing role by its id.
     *
     * @param NtUid $id
     * @return Role
     */
    public function get(NtUid $id);

    /**
     * @param NtUid $id
     * @return StaffRole
     */
    public function getStaffRole(NtUid $id);

    /**
     * @param $staffRole
     * @return int Number of rows affected
     */
    public function updateStaffRole(StaffRole $staffRole);

}