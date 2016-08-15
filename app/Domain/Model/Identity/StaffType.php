<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 28/06/16
 * Time: 09:23
 */

namespace App\Domain\Model\Identity;


use JMS\Serializer\JsonSerializationVisitor;
use MyCLabs\Enum\Enum;

use JMS\Serializer\Annotation\HandlerCallback;

class StaffType extends Enum
{
    const TEACHER = 'T';
    const TITULAR = 'X';

    /**
     * @HandlerCallback("json",  direction = "serialization")
     *
     * @param JsonSerializationVisitor $visitor
     * @return array
     */
    public function serializeToJson(JsonSerializationVisitor $visitor)
    {
        $visitor->addData('type', $this->getValue());
    }
}