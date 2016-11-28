<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 25/11/16
 * Time: 09:01
 */

namespace App\Pdf\NtPdf;


class Tools
{
    public static function getValue( array $var, $index = '', $default = '' )
    {
        if ( isset( $var[ $index ] ) )
        {
            return $var[ $index ];
        }

        return $default;
    }

    
    /**
     * Get the next value from the array
     *
     * @param array $data
     * @param number $index
     * @return mixed
     */
    public static function getNextValue( array $data, &$index )
    {
        if ( isset( $index ) )
        {
            $index++;
        }

        if ( !isset( $index ) || ( $index >= count( $data ) ) )
        {
            $index = 0;
        }

        return $data[ $index ];
    }

    /**
     * Returns the color array of the 3 parameters or the 1st param if the others are not specified
     *
     * @param int|false $r
     * @param int|null $b
     * @param int|null $g
     * @return array|false
     */
    public static function getColor( $r, $b = null, $g = null )
    {
        if ( $g !== null && $b !== null )
        {
            return array( $r, $b, $g );
        }

        return $r;
    }

    /**
     * Returns an array. If the input paramter is array then this array will be returned.
     * Otherwise a array($value) will be returned;
     *
     * @param mixed $value
     * @return array
     */
    public static function makeArray( $value )
    {
        if ( is_array( $value ) )
        {
            return $value;
        }

        return array( $value );
    }


    /**
     * Returns TRUE if value is FALSE(0, '0', FALSE)
     *
     * @param mixed $value
     * @return bool
     */
    public static function isFalse( $value )
    {
        if ( false === $value )
            return true;

        if ( 0 === $value )
            return true;

        if ( '0' === $value )
            return true;

        return false;
    }
}