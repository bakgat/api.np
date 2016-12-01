<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 25/11/16
 * Time: 21:08
 */

namespace App\Domain\Services\Pdf;


class Colors
{
    const ORANGE = [231, 155, 0];
    const BLUE = [0, 87, 157];
    const PINK = [244,34,206];
    const GREEN = [27, 150, 0];
    const RED = [255, 20, 0];
    const BLACK= [0, 0, 0];


    public static function transparencyToRGB(array $color, $transparency = 200, $background = 255) {
        $res = [0,0,0];
        for($i=0;$i<count($color);$i++) {
            $alpha = $transparency / 255;
            $oneminusalpha = 1 - $alpha;
            $res[$i] = (($color[$i] * $alpha) + ($oneminusalpha * $background));
        }
        return $res;
    }

    public static function lblue() {
        return self::transparencyToRGB(self::BLUE, 210);
    }
    public static function llblue() {
        return self::transparencyToRGB(self::BLUE, 130);
    }
    public static function lllblue() {
        return self::transparencyToRGB(self::BLUE, 50);
    }

    public static function str_blue() {
        return implode(',', self::BLUE);
    }
    public static function str_lblue()
    {
        return implode(',', self::lblue());
    }
    public static function str_llblue()
    {
        return implode(',', self::llblue());
    }
    public static function str_lllblue()
    {
        return implode(',', self::lllblue());
    }


    public static function str_orange()
    {
        return implode(',', self::ORANGE);
    }

    public static function str_green()
    {
        return implode(',', self::GREEN);
    }
    public static function str_pink() {
        return implode(',', self::PINK);
    }
    public function str_black() {
        return implode(',', self::BLACK);
    }


}