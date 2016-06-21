<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 21/06/16
 * Time: 21:13
 */

namespace App\Domain;


interface ValueObject
{
    /**
     * Determine equality with another object
     *
     * @param ValueObject $object
     * @return bool
     */
    public function equals(ValueObject $object);
}