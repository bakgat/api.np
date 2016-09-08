<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 24/06/16
 * Time: 09:08
 */

namespace  App\Domain\Model\Education\Exceptions;


use App\Domain\NtUid;
use \Exception;

class BranchNotFoundException extends Exception
{

    /**
     * BranchNotFoundException constructor.
     * @param NtUid $id
     */
    public function __construct($id)
    {
        $msg = 'Branch with id (' . $id . ') was not found.';
        parent::__construct($msg);
    }
}