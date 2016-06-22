<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 22/06/16
 * Time: 16:16
 */

namespace App\Domain\Model\Identity;


use Webpatser\Uuid\Uuid;

interface StudentRepository
{
    /**
     * Gets all the active students.
     *
     * @return ArrayCollection|Student[]
     */
    public function all();

    /**
     * Finds a student by its id, if not returns null
     *
     * @param $id
     * @return Student|null
     */
    public function find($id);

    /**
     * Gets an existing student by its id
     *
     * @param $id
     * @return Student
     */
    public function get($id);
}