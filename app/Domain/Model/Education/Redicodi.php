<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 1/08/16
 * Time: 15:56
 */

namespace App\Domain\Model\Education;


use MyCLabs\Enum\Enum;

class Redicodi extends Enum
{
    const BASIC = 'B';
    const CHALLENGE = 'C';
    const SUPPORT = 'S';
    const TOOLS = 'T';
}