<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 6/08/16
 * Time: 23:00
 */

namespace App\Domain\Model\Evaluation;


use App\Domain\Model\Identity\Group;
use App\Domain\NtUid;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

interface EvaluationRepository
{
    public function allEvaluationsForGroup(Group $group, DateTime $start, DateTime $end);

    /**
     * @param NtUid $id
     * @return Evaluation
     */
    public function get(NtUid $id);

    /**
     * @param $evaluation
     * @return int
     */
    public function update(Evaluation $evaluation);

    /**
     * @param $evaluation
     * @return NtUid
     */
    public function insert(Evaluation $evaluation);

    /**
     * @return ArrayCollection
     */
    public function getSummary();
}