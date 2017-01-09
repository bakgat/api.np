<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 7/11/16
 * Time: 13:16
 */

namespace App\Domain\Model\Evaluation;


use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\Goal;
use App\Domain\Model\Education\Major;
use App\Domain\Model\Identity\Group;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Time\DateRange;
use App\Domain\NtUid;
use Doctrine\Common\Collections\ArrayCollection;

interface IACRepository
{
    /**
     * @param NtUid $id
     * @return IAC
     */
    public function get(NtUid $id);

    public function insert(IAC $iac);

    public function update(IAC $iac);

    public function allGoals();

    public function allGoalsForMajor(Major $major);

    public function allGoalsForBranch(Branch $branch);

    /**
     * @param Student $student
     * @return ArrayCollection
     */
    public function getFlatIacForStudent($studentId, $range);

    /**
     * @param $group
     * @return ArrayCollection
     */
    public function getFlatIacForGroup($groupId, $range);

    /**
     * @param Group $group
     * @param $infinite
     * @return ArrayCollection
     */
    public function getIacForGroup(Group $group, DateRange $range);

    /**
     * @param $studentId
     * @param $infinite
     * @return IAC
     */
    public function getIacForStudent($studentId, $infinite);



    public function getFlatIacForStudents($studentIds, $range);

    /**
     * @return ArrayCollection
     */
    public function getIacs();

    /**
     * @param NtUid $id
     * @return Goal
     */
    public function getGoal(NtUid $id);

    /**
     * @param $iacId
     * @return mixed
     */
    public function remove(IAC $iacId);


}