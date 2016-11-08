<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 7/11/16
 * Time: 13:16
 */

namespace App\Domain\Model\Evaluation;


use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\Major;
use App\Domain\Model\Identity\Student;
use Doctrine\Common\Collections\ArrayCollection;

interface IACRepository
{
    public function allGoals();

    public function allGoalsForMajor(Major $major);

    public function allGoalsForBranch(Branch $branch);

    /**
     * @param Student $student
     * @return ArrayCollection
     */
    public function iacForStudent(Student $student);

    /**
     * @return ArrayCollection
     */
    public function getIacs();


}