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
     * Finds a student by its id, if not returns null.
     *
     * @param $id
     * @return Student|null
     */
    public function find($id);

    /**
     * Gets an existing student by its id.
     *
     * @param $id
     * @return Student
     */
    public function get($id);

    /**
     * Saves a new student.
     *
     * @param Student $student
     * @return Uuid
     */
    public function insert(Student $student);

    /**
     * Saves an existing student.
     *
     * @param Student $student
     * @return int Number of affected rows
     */
    public function update(Student $student);

    /**
     * Deletes an existing student.
     *
     * @param $id
     * @return int Number of affected rows.
     */
    public function delete(Uuid $id);
}