<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 24/06/16
 * Time: 09:08
 */

namespace  App\Domain\Model\Education\Exceptions;


use \Exception;

class MajorNotFoundException extends Exception
{

    /**
     * MajorNotFoundException constructor.
     * @param Uuid $id
     */
    public function __construct($id)
    {
        $msg = 'Major with id (' . $id . ') was not found.';
        parent::__construct($msg);
    }
}