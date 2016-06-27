<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 27/06/16
 * Time: 15:40
 */

namespace App\Domain\Model\Identity;


use MyCLabs\Enum\Enum;

class Gender extends Enum
{
    const MALE = 'M';
    const FEMALE = 'F';
    const OTHER = 'O';
}
