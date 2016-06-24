<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 24/06/16
 * Time: 09:08
 */

namespace  App\Domain\Model\Identity\Exceptions;


use \Exception;

class StudentNotFoundException extends Exception
{

    /**
     * StudentNotFoundException constructor.
     * @param Uuid $id
     */
    public function __construct($id)
    {
        $msg = 'Student with id (' . $id . ') was not found.';
        parent::__construct($msg);
    }
}