<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 22/06/16
 * Time: 16:16
 */

namespace App\Domain\Model\Identity;


interface StudentRepository
{
    /**
     * Gets all the active students.
     *
     * @return ArrayCollection|Student[]
     */
    public function all();
}