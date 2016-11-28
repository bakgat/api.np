<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 27/11/16
 * Time: 09:46
 */

namespace App\Pdf\NtPdf;


class Listener
{
    public $headerText;

    public $pdf;

    public $callBack;

    public function notify()
    {
        call_user_func( $this->callBack, $this->pdf );
    }

    public function setText($text)
    {
        $this->headerText = $text;
    }

    public function on($callBack)
    {
        $this->callBack = $callBack;
    }
}