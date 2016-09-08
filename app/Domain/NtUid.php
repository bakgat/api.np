<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 30/07/16
 * Time: 14:23
 */

namespace App\Domain;


use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Annotation\HandlerCallback;

class NtUid extends \Webpatser\Uuid\Uuid
{
    public function __construct($uuid)
    {
        parent::__construct($uuid);
    }


    /**
     * Import an existing UUID
     *
     * @param string $uuid
     * @return NtUid
     */
    public static function import($uuid)
    {
        return new static(static::makeBin($uuid, 16));
    }

    /**
     * @HandlerCallback("json",  direction = "serialization")
     *
     * @param JsonSerializationVisitor $visitor
     * @return array
     */
    public function serializeToJson(JsonSerializationVisitor $visitor)
    {
        $visitor->addData('id', $this->string);
    }

    public function toString() {
        return $this->string;
    }
}