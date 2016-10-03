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
    
    /*
    {
    "id": "P",
    "name": "filosofie",
    "icon": "notos notos-philosophy"
    }, {
        "id": "SF",
        "name": "zonnebloemklas",
        "icon": "notos notos-sunflower"
    }, {
        "id": "MSF",
        "name": "mini-zonnebloemklas",
        "icon": "notos notos-mini-sunflower"
    }, {
        "id": "M",
        "name": "rekenmonster",
        "icon": "notos notos-monster"
    }, {
        "id": "BF",
        "name": "vlinderklas",
        "icon": "notos notos-butterfly"
    }, {
        "id": "RT",
        "name": "leestrein",
        "icon": "notos notos-abc-train"
    }, {
        "id": "MT",
        "name": "rekentrein",
        "icon": "notos notos-math-train"
    }
     * */

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
}