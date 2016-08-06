<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 6/08/16
 * Time: 23:00
 */

namespace App\Domain\Model\Evaluation;


use App\Domain\Model\Identity\Group;

interface EvaluationRepository
{
    public function allEvaluationsForGroup(Group $group);
}