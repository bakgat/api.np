<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 28/06/16
 * Time: 09:23
 */

namespace App\Domain\Model\Identity;


use MyCLabs\Enum\Enum;

class StaffType extends Enum
{
    const TEACHER = 'T';
    const TITULAR = 'X';
    const MANAGER = 'M';
    const SECRETARY = 'S';
}