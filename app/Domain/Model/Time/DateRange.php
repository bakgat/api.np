<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 21/06/16
 * Time: 21:12
 */

namespace App\Domain\Model\Time;


use App\Domain\ValueObject;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;

class DateRange
{
    /**
     * Far Future ISO-8601 date
     *
     * @var string
     */
    const FUTURE = '9999-12-31';
    /**
     * Far Past ISO 8601 date
     *
     * @var string
     */
    const PAST = '1000-01-01';

    /** @var DateTime */
    protected $start;
    /** @var DateTime */
    protected $end;

    public function __construct(DateTime $start, DateTime $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * @return DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Build a DateRange object from an ISO 8601 interval string.
     *
     * Currently only expects Y-m-d/Y-m-d
     *
     * @param $string string ISO-8601 interval string
     * @return DateRange
     */
    public static function fromIso8601($string)
    {
        $split = explode('/', $string, 2);
        if (count($split) !== 2) {
            throw new InvalidArgumentException(
                'The format is expected to be Y-m-d/Y-m-d'
            );
        }

        return new static(
            new DateTime($split[0]),
            new DateTime($split[1])
        );
    }

    /**
     * Build a DateRange object from existing data
     *
     * This accepts an array or object and assumes members 'start' and 'end'
     * somewhere in the array or object. You can override these values with
     * whatever you like.
     *
     * <pre>
     * // Example usage
     * $array = array('start' => '2009-05-06', 'end' => new DateTime('2009-06-07'));
     *
     * $object = new stdClass();
     * $object->start = '2009-05-06';
     * $object->end = new DateTime('2009-06-07');
     *
     * $range1 = DateRange::fromData($array);
     * $range2 = DateRange::fromData($object);
     * </pre>
     *
     * @param $data |object  $object
     * @param string $start 'Start' member or index name
     * @param string $end 'End' member or index name
     * @return DateRange
     */
    public static function fromData($data, $start = 'start', $end = 'end')
    {
        if (is_object($data)) {
            $is_object = true;
        } else if (is_array($data)) {
            $is_object = false;
        } else {
            throw new InvalidArgumentException(
                'You must either pass an object or an array as the first parameter'
            );
        }

        $start_dt = null;
        $end_dt = null;
        if ($is_object) {
            if (isset($data->{$start})) {
                if ($data->{$start} instanceof DateTime) {
                    $start_dt = clone $data->{$start};
                } else {
                    $start_dt = new DateTime($data->{$start});
                }
            }
            if (isset($data->{$end})) {
                if ($data->{$end} instanceof DateTime) {
                    $end_dt = clone $data->{$end};
                } else {
                    $end_dt = new DateTime($data->{$end});
                }
            }
        } else {
            if (isset($data[$start])) {
                if ($data[$start] instanceof DateTime) {
                    $start_dt = clone $data[$start];
                } else {
                    $start_dt = new DateTime($data[$start]);
                }
            }
            if (isset($data[$end])) {
                if ($data[$end] instanceof DateTime) {
                    $end_dt = clone $data[$end];
                } else {
                    $end_dt = new DateTime($data[$end]);
                }
            }
        }

        if (is_null($start_dt) && is_null($end_dt)) {
            $date_range = static::infinite();
        } else if (is_null($start_dt)) {
            $date_range = static::upTo($end_dt);
        } else if (is_null($end_dt)) {
            $date_range = static::startingOn($start_dt);
        } else {
            $date_range = new static($start_dt, $end_dt);
        }

        return $date_range;
    }

    /**
     * Create the infinite date range
     *
     * note: internally a finite, but unusual boundary is used
     *
     * @return DateRange
     */
    public static function infinite()
    {
        return new static(new DateTime(static::PAST), new DateTime(static::FUTURE));
    }

    /**
     * Create a date range with an unbounded past, but a bounded future
     *
     * @param DateTime $end
     * @return DateRange
     */
    public static function upTo(DateTime $end)
    {
        return new static(new DateTime(static::PAST), $end);
    }

    /**
     * Create a date range with an bounded past, but a unbounded future
     *
     * @param DateTime $start
     * @return DateRange
     */
    public static function startingOn(DateTime $start)
    {
        return new static($start, new DateTime(static::FUTURE));
    }

    /**
     * Test whether this range represents an empty range
     *
     * This is primarily used internally, but other methods may set the range
     * to empty. This usually signals some kind of error where the return value
     * is expected to be a DateRange and can be tested for emptiness.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->getEnd() < $this->getStart();
    }

    /**
     * Determine equality with another object
     *
     * @param DateRange $arg
     * @return bool
     */
    public function equals(DateRange $arg)
    {
        return $this->getStart() == $arg->getStart()
        && $this->getEnd() == $arg->getEnd();
    }

    /**
     * Test whether this DateRange includes a DateTime or a DateRange
     *
     * If a DateTime is greater than or equal to the start of AND less than
     * or equal to the end of this DateRange, it is considered included.
     *
     * If a DateRange is fully enclosed inside this DateRange, it is
     * considered included. The test is essentially the same as for the
     * DateTime except it is performed on both the start and end dates of the
     * DateRange.
     *
     * @param  DateTime|DateRange $arg Other object to test
     * @return bool
     */
    public function includes($arg)
    {
        if ($arg instanceof DateTime) {
            return $this->getStart() <= $arg
             && $this->getEnd() >= $arg;
        } else if ($arg instanceof DateRange) {
            return $this->includes($arg->getStart())
                && $this->includes($arg->getEnd());
        } else {
            throw new InvalidArgumentException(
                'Argument must be an instance of DateTime or ' . __CLASS__
            );
        }
    }

    /**
     * Convert the DateRange to an ISO-8601 interval string
     *
     * http://en.wikipedia.org/wiki/ISO_8601#Time_intervals
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->isEmpty()) {
            return '';
        }

        return implode(
            '/',
            [
                $this->getStart()->format('Y-m-d'),
                $this->getEnd()->format('Y-m-d')
            ]
        );
    }

    /**
     * Determine if the range is anchored in the future
     *
     * @return bool
     */
    public function isFuture()
    {
        return new DateTime(static::FUTURE) == $this->getEnd();
    }

    /**
     * Determine if the range is anchored in the past
     *
     * @return bool
     */
    public function isPast()
    {
        return new DateTime(static::PAST) == $this->getStart();
    }

    /**
     * Determine if the range is infinitive
     *
     * @return bool
     */
    public function isInfinite()
    {
        return static::infinite() == $this;
    }
}