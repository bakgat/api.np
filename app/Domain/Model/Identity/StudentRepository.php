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
     * Gets a list of id/given field for all students.
     * ie generate a list of id and email-addresses to check client-side uniqueness
     *
     * @param $field
     * @return ArrayCollection|Student[]
     */
    public function flat($field);

    /**
     * Finds a student by its id, if not returns null.
     *
     * @param Uuid $id
     * @return Student|null
     */
    public function find(Uuid $id);

    /**
     * Gets an existing student by its id.
     *
     * @param Uuid $id
     * @return Student
     */
    public function get(Uuid $id);

    /**
     * Gets all groups where a student was member of.
     *
     * @param Uuid $id
     * @return array
     */
    public function allGroups(Uuid $id);

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
     * @return int Number of affected rows.
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