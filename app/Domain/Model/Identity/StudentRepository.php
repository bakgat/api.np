<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 22/06/16
 * Time: 16:16
 */

namespace App\Domain\Model\Identity;


use App\Domain\Model\Evaluation\RedicodiForStudent;
use App\Domain\NtUid;
use DateTime;
use Doctrine\Common\Collections\Collection;

interface StudentRepository
{
    /**
     * Gets all the active students.
     *
     * @return Collection
     */
    public function all();

    /**
     * Gets all the active students in a group.
     *
     * @param Group $group
     * @param DateTime|null $date
     * @return Collection
     */
    public function allActiveInGroup(Group $group, $date=null);

    /**
     * Gets a list of id/given field for all students.
     * ie generate a list of id and email-addresses to check client-side uniqueness
     *
     * @param $field
     * @return Collection
     */
    public function flat($field);

    /**
     * Finds a student by its id, if not returns null.
     *
     * @param NtUid $id
     * @return Student|null
     */
    public function find(NtUid $id);

    /**
     * Gets an existing student by its id.
     *
     * @param NtUid $id
     * @return Student
     */
    public function get(NtUid $id);

    /**
     * Gets all groups where a student was member of.
     *
     * @param NtUid $id
     * @return array
     */

    // TODO Unused

    //public function allGroups(NtUid $id);



    /**
     * Saves a new student.
     *
     * @param Student $student
     * @return NtUid
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
    public function delete(NtUid $id);

    /* ***************************************************
     * REDICODI
     * **************************************************/

    /**
     * Gets all 'redicodi' applicale for a given student.
     *
     * @param NtUid $id
     * @return RedicodiForStudent[]
     */
    //TODO UNUSED
    //public function allRedicodi(NtUid $id);

    /**
     * @param NtUid $id
     * @return RedicodiForStudent
     */
    public function getStudentRedicodi(NtUid $id);

    /**
     *
     * @param RedicodiForStudent $studentRedicodi
     * @return int
     */
    public function updateRedicodi(RedicodiForStudent $studentRedicodi);


}