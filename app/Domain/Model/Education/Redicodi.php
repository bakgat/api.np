<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 1/08/16
 * Time: 15:56
 */

namespace App\Domain\Model\Education;


use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Annotation\HandlerCallback;

use MyCLabs\Enum\Enum;

class Redicodi extends Enum
{
    const BASIC = 'B';
    const CHALLENGE = 'C';
    const SUPPORT = 'S';
    const TOOLS = 'T';
    const PHILOSOPHY = 'P';
    const SUNFLOWER = 'SF';
    const MINISUNFLOWER = 'MSF';
    const MATHMONSTER = 'M';
    const BUTTERFLY = 'BF';
    const READTRAIN = 'RT';
    const MATHTRAIN = 'MT';
    const TIGER = 'TGR';
    const IAC = 'IAC';
    const BEE = 'BEE';


    /**
     * @HandlerCallback("json",  direction = "serialization")
     *
     * @param JsonSerializationVisitor $visitor
     * @return array
     */
    public function serializeToJson(JsonSerializationVisitor $visitor)
    {
        $visitor->addData('redicodi', $this->getValue());
    }

    public static function icon($redicodi)
    {
        switch ($redicodi) {
            case Redicodi::BASIC:
                return 'g';
            case Redicodi::CHALLENGE:
                return 'l';
            case Redicodi::SUPPORT:
                return 'd';
            case Redicodi::TOOLS:
                return 'f';
            case Redicodi::PHILOSOPHY:
                return 'e';
            case Redicodi::SUNFLOWER:
                return 'c';
            case Redicodi::MINISUNFLOWER:
                return 'b';
            case Redicodi::MATHMONSTER:
                return 'a';
            case Redicodi::BUTTERFLY:
                return 'j';
            case Redicodi::READTRAIN:
                 return 'h';
            case Redicodi::MATHTRAIN:
                return 'i';
            case Redicodi::TIGER:
                return 'p';
            case Redicodi::IAC:
                return 'm';
            case Redicodi::BEE:
                return 'q';
        }
    }
}