<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 30/07/16
 * Time: 14:23
 */

namespace App\Domain;


use Exception;
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
     * @param int $ver
     * @param string $node
     * @param string $ns
     * @return NtUid
     * @throws Exception
     */
    public static function generate($ver = 1, $node = null, $ns = null)
    {
        /* Create a new NtUid based on provided data. */
        switch ((int)$ver) {
            case 1:
                return new static(static::mintTime($node));
            case 2:
                // Version 2 is not supported
                throw new Exception('Version 2 is unsupported.');
            case 3:
                return new static(static::mintName(static::MD5, $node, $ns));
            case 4:
                return new static(static::mintRand());
            case 5:
                return new static(static::mintName(static::SHA1, $node, $ns));
            default:
                throw new Exception('Selected version is invalid or unsupported.');
        }
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