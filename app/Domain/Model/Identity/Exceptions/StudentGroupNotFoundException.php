<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 24/06/16
 * Time: 09:08
 */

namespace  App\Domain\Model\Identity\Exceptions;


use App\Domain\NtUid;
use \Exception;

class StudentGroupNotFoundException extends Exception
{

    /**
     * StudentGroupNotFoundException constructor.
     * @param NtUid $id
     */
    public function __construct($id)
    {
        $msg = 'StudentGorup with id (' . $id . ') was not found.';
        parent::__construct($msg);
    }
}