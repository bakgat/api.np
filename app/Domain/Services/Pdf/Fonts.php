<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 25/11/16
 * Time: 21:13
 */

namespace App\Domain\Services\Pdf;


class Fonts
{
    const ALL = [
        ['name' => 'RobotoThin', 'style' => '', 'file' => 'RobotoCondensed-Light.php'],
        ['name' => 'RobotoThin', 'style' => 'b', 'file' => 'Roboto-Medium.php'],
        ['name' => 'Roboto', 'style' => 'i', 'file' => 'Roboto-Italic.php'],
        ['name' => 'Roboto', 'style' => '', 'file' => 'Roboto-Regular.php'],
        ['name' => 'Roboto', 'style' => 'b', 'file' => 'Roboto-Bold.php'],
        ['name' => 'NotosIcon', 'style' => '', 'file' => 'NotosIcons.php']
    ];
}