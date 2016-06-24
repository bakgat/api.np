<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 24/06/16
 * Time: 15:46
 */

namespace App\Domain\Model\Identity\Exceptions;


class GroupNameNotUniqueException extends \Exception
{

    /**
     * GroupNameNotUniqueException constructor.
     * @param string $name
     */
    public function __construct($name)
    {
        $msg = 'Group with name (' . $name . ') does already exist.';
        parent::__construct($msg);
    }
}
{

}