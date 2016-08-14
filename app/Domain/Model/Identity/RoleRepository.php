<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 13/08/16
 * Time: 20:58
 */

namespace App\Domain\Model\Identity;


use App\Domain\Uuid;
use Doctrine\Common\Collections\ArrayCollection;

interface RoleRepository
{
    /**
     * Gets all the roles available.
     *
     * @return ArrayCollection
     */
    public function all();

}