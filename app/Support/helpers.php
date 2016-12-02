<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 8/08/16
 * Time: 09:53
 */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

if (!function_exists('collection_average')) {
    /**
     * Returns average of a given field in a collection.
     *
     * @param Collection $collection
     * @param string $field
     * @return float
     */
    function collection_average(Collection $collection, $field)
    {
        $avg = 0;
        if ($collection->count() === 0) return 0;

        $collection->forAll(function ($index, $element) use (&$avg, $field) {
            $obj = new \ReflectionObject($element);
            $prop = $obj->getProperty($field);
            $prop->setAccessible(true);

            $avg += $prop->getValue($element);
            return true;
        });

        return $avg / $collection->count();
    }
}

if (!function_exists('collection_median')) {
    /**
     * Returns median of a given field in a collection.
     *
     * @param Collection $collection
     * @param string $field
     * @return float
     */
    function collection_median(Collection $collection, $field)
    {
        $iterator = sorted_iterator_on($collection, $field);


        if ($iterator->count() === 0) return 0;
        if ($iterator->count() % 2 === 0) {

            $prev = $iterator[ceil($iterator->count() / 2) - 1];
            $next = $iterator[ceil($iterator->count() / 2)];

            $obj = new \ReflectionObject($prev);
            $prop = $obj->getProperty($field);
            $prop->setAccessible(true);


            return ($prop->getValue($prev) + $prop->getValue($next)) / 2;
        } else {
            $element = $iterator[floor($iterator->count() / 2)];
            $obj = new \ReflectionObject($element);
            $prop = $obj->getProperty($field);
            $prop->setAccessible(true);

            return $prop->getValue($element);
        }
    }
}


if (!function_exists('sorted_iterator_on')) {
    /**
     * @param Collection $collection
     * @param $field
     * @return ArrayIterator
     */
    function sorted_iterator_on(Collection $collection, $field)
    {
        $iterator = $collection->getIterator();
        $iterator->uasort(function ($first, $second) use ($field) {
            $fObj = new \ReflectionObject($first);
            $fProp = $fObj->getProperty($field);
            $fProp->setAccessible(true);

            $sObj = new \ReflectionObject($second);
            $sProp = $sObj->getProperty($field);
            $sProp->setAccessible(true);

            $firstPrice = abs($fProp->getValue($first));
            $secondPrice = abs($sProp->getValue($second));

            if ($firstPrice == $secondPrice) return 0;
            else return ($firstPrice < $secondPrice ? -1 : 1);
        });
        $list = iterator_to_array($iterator, false);
        return new \ArrayIterator($list);
    }
}

if (!function_exists('convert_date_from_string')) {
    function convert_date_from_string($sDate)
    {
        $date = strtotime($sDate);
        if ($date != false) {
            $date = new DateTime(date('Y-m-d', $date));
        }
        return $date;
    }
}

if (! function_exists('resource_path')) {
    /**
     * Get the path to the resources folder.
     *
     * @param  string  $path
     * @return string
     */
    function resource_path($path = '')
    {
        return app()->basePath().DIRECTORY_SEPARATOR.'resources'.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}