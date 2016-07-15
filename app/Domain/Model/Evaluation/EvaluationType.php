<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 15/07/16
 * Time: 06:46
 */

namespace App\Domain\Model\Evaluation;


use MyCLabs\Enum\Enum;

class EvaluationType extends Enum
{
    const POINT = 'P';
    const COMPREHENSIVE = 'C';
    const FEEDBACK = 'F';
}