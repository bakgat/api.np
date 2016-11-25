<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 25/11/16
 * Time: 09:02
 */

namespace App\Pdf\NtPdf;


class Validate
{
    /**
     * Returns a positive(>0) integer value
     *
     * @param $value
     * @return int
     */
    public static function intPositive( $value )
    {
        $value = intval( $value );
        if ( $value < 1 ) $value = 1;

        return $value;
    }


    /**
     * Returns a float value.
     * If min and max are specified, then $value will have to be between $min and $max
     *
     * @param float $value
     * @param null|float $min
     * @param null|float $max
     * @return float
     */
    public static function float( $value, $min = null, $max = null )
    {
        $value = floatval( $value );

        if ( $min !== null )
        {
            $min = floatval( $min );
            if ( $value < $min ) return $min;
        }

        if ( $max !== null )
        {
            $max = floatval( $max );
            if ( $value > $max ) return $max;
        }

        return $value;
    }


    /**
     * Validates the align Vertical value
     *
     * @param $value
     * @return string
     */
    public static function alignVertical( $value )
    {
        $value = strtoupper( $value );

        $aValid = array( 'T', 'B', 'M' );

        if ( !in_array( $value, $aValid ) )
        {
            return 'M';
        }

        return $value;
    }
}