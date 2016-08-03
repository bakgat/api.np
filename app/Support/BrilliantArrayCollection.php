<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 3/08/16
 * Time: 14:56
 */

namespace App\Support;


use Doctrine\Common\Collections\ArrayCollection;

class BrilliantArrayCollection extends ArrayCollection
{
    /**
     * Reduce the collection into a single value.
     *
     * @param \Closure $func
     * @param null $initialValue
     * @return mixed
     */
    public function reduce(\Closure $func, $initialValue = null)
    {
        return array_reduce($this->toArray(), $func, $initialValue);
    }

    /**
     * Apply filter chain
     *
     * @var \Closure[] $chain
     * @return BrillianArrayCollection
     */
    public function applyFilterChain($chain)
    {
        $collection = $this;
        foreach ($chain as $filter) {
            $collection = $collection->filter($filter);
        }
        return $collection;
    }

    /**
     * Get average
     *
     * @param $field
     * @return float
     */
    public function average($field)
    {
        $avg = 0;
        if ($this->count() === 0) return 0;

        $this->forAll(function ($index, $element) use (&$avg, $field) {
            $obj = new \ReflectionObject($element);
            $prop = $obj->getProperty($field);
            $prop->setAccessible(true);

            $avg += $prop->getValue($element);
            return true;
        });

        return $avg / $this->count();
    }

    /**
     * Get median.
     *
     * @param $field
     * @return float
     */
    public function median($field)
    {
        $iterator = $this->getSortedIteratorOn($field);


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

    /**
     * Get standard deviation.
     *
     * @param $field
     * @return float
     */
    public function standardDeviation($field)
    {
        $average = $this->average($field);
        $variance = 0;
        if ($this->count() === 0) return 0;
        $this->forAll(function ($index, $element) use (&$variance, $average, $field) {
            $obj = new \ReflectionObject($element);
            $prop = $obj->getProperty($field);
            $prop->setAccessible(true);

            $variance += pow($prop->getValue($element) - $average, 2);
            return true;
        });
        $variance /= $this->count();
        return sqrt($variance);
    }

    /**
     * Get absolute median. Make negative into position
     *
     * @param $field
     * @return float
     */
    public function getAbsoluteMean($field)
    {
        $iterator = $this->getSortedAbsoluteIteratorOn($field);
        if ($iterator->count() === 0) return 0;
        if ($iterator->count() % 2 === 0) {
            $prev = $iterator[ceil($iterator->count() / 2) - 1];
            $next = $iterator[ceil($iterator->count() / 2)];

            $obj = new \ReflectionObject($prev);
            $prop = $obj->getProperty($field);
            $prop->setAccessible(true);

            return (abs($prop->getValue($prev)) + abs($prop->getValue($next))) / 2;
        } else {
            $element = $iterator[floor($iterator->count() / 2)];
            $obj = new \ReflectionObject($element);
            $prop = $obj->getProperty($field);
            $prop->setAccessible(true);

            return abs($prop->getValue($element));
        }
    }

    public function getSortedAbsoluteIteratorOn($field)
    {
        $iterator = $this->getIterator();
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

    /**
     * Get a sorted iterator on a field.
     *
     * @param string $field
     * @return \ArrayIterator
     */
    public function getSortedIteratorOn($field)
    {
        $iterator = $this->getIterator();
        $iterator->uasort(function ($first, $second) use ($field) {
            $fObj = new \ReflectionObject($first);
            $fProp = $fObj->getProperty($field);
            $fProp->setAccessible(true);

            $sObj = new \ReflectionObject($second);
            $sProp = $sObj->getProperty($field);
            $sProp->setAccessible(true);

            $firstPrice = $fProp->getValue($first);
            $secondPrice = $sProp->getValue($second);

            if ($firstPrice == $secondPrice) return 0;
            else return ($firstPrice < $secondPrice ? -1 : 1);
        });
        $list = iterator_to_array($iterator, false);
        return new \ArrayIterator($list);
    }


    /**
     * Return a new collection with sorted result.
     *
     * @param string $field
     * @return PropertyCollection
     */
    public function getSortedCollection($field)
    {
        return new self($this->getSortedIteratorOn($field)->getArrayCopy());
    }

    /**
     * Return a new collection excluding the outlier.
     *
     * @param $median
     * @param float $sdRange The Standard Deviation range.
     * @param string $field
     * @return self
     */
    public function excludeOutlier($median, $sdRange, $field)
    {
        $from = $median / 2;
        $to = $median + $sdRange;
        return $this->filter(function ($e) use ($from, $to, $field) {
            $obj = new \ReflectionObject($e);
            $prop = $obj->getProperty($field);
            $prop->setAccessible(true);

            $price = 0;
            if ($prop->getValue($e) !== null) {
                $price = $prop->getValue($e);
            }
            if ($price >= $from && $price <= $to) return true;
            else return false;
        });
    }

}