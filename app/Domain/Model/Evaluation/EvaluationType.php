<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 15/07/16
 * Time: 06:46
 */

namespace App\Domain\Model\Evaluation;


use JMS\Serializer\Annotation\HandlerCallback;
use JMS\Serializer\JsonSerializationVisitor;
use MyCLabs\Enum\Enum;

class EvaluationType extends Enum
{
    const POINT = 'P';
    const COMPREHENSIVE = 'C';
    const MULTIPLECHOICE = 'MC';
    const SPOKEN = 'S';
    const FEEDBACK = 'F';

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