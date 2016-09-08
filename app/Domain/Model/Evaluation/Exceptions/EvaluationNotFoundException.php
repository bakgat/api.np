<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 24/06/16
 * Time: 09:08
 */

namespace  App\Domain\Model\Evaluation\Exceptions;


use App\Domain\NtUid;
use \Exception;

class EvaluationNotFoundException extends Exception
{

    /**
     * EvaluationNotFoundException constructor.
     * @param NtUid $id
     */
    public function __construct($id)
    {
        $msg = 'Evaluation with id (' . $id . ') was not found.';
        parent::__construct($msg);
    }
}