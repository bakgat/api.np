<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 15/07/16
 * Time: 07:01
 */

namespace App\Domain\Model\Education\Exceptions;


class MaxNullException extends \Exception
{
    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        $exception = null;

        parent::__construct('Max can\'t be null when evalutionType is point', 0, $exception);

    }
}