<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 13/08/16
 * Time: 21:00
 */

namespace App\Domain\Model\Identity;


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
     * Get all active staff members active in a role.
     *
     * @param Role $group
     * @return ArrayCollection
     */
    public function allInRole(Role $group);

}