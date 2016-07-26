<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 27/06/16
 * Time: 15:40
 */

namespace App\Domain\Model\Identity;


use MyCLabs\Enum\Enum;


use JMS\Serializer\Annotation\HandlerCallback;
use JMS\Serializer\JsonSerializationVisitor;

class Gender extends Enum
{
    const MALE = 'M';
    const FEMALE = 'F';
    const OTHER = 'O';

    /**
     * @HandlerCallback("json",  direction = "serialization")
     *
     * @param JsonSerializationVisitor $visitor
     * @return array
     */
    public function serializeToJson(JsonSerializationVisitor $visitor)
    {
        $visitor->addData('gender', $this->getValue());
    }
}
