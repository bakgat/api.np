<?php
use App\Domain\Model\Time\DateRange;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 21/06/16
 * Time: 22:07
 */
class DateRangeTest extends TestCase
{
    /**
     * @test
     * @group time
     * @group daterange
     */
    public function should_create_from_ISO_8601()
    {
        $dr = DateRange::fromIso8601('2015-09-01/2016-06-30');

        $this->assertEquals(new DateTime('2015-09-01'), $dr->getStart());
        $this->assertEquals(new DateTime('2016-06-30'), $dr->getEnd());

        $this->setExpectedException('InvalidArgumentException');
        $dr = DateRange::fromIso8601('2015-09-01');
    }

    /**
     * @test
     * @group time
     * @group daterange
     */
    public function should_create_from_data_array()
    {
        $arr = ['start' => '2015-09-01', 'end' => '2016-06-30'];
        $dr = DateRange::fromData($arr);

        $this->assertEquals(new DateTime('2015-09-01'), $dr->getStart());
        $this->assertEquals(new DateTime('2016-06-30'), $dr->getEnd());

        $arr = ['start' => '2015-09-01'];
        $dr = DateRange::fromData($arr);

        $this->assertEquals(new DateTime('2015-09-01'), $dr->getStart());
        $this->assertEquals(new DateTime(DateRange::FUTURE), $dr->getEnd());

        $arr = ['end' => '2016-06-30'];
        $dr = DateRange::fromData($arr);

        $this->assertEquals(new DateTime(DateRange::PAST), $dr->getStart());
        $this->assertEquals(new DateTime('2016-06-30'), $dr->getEnd());
    }

    /**
     * @test
     * @group time
     * @group daterange
     */
    public function should_create_from_data_object()
    {
        $obj = new stdClass;
        $obj->start = new DateTime('2015-09-01');
        $obj->end = new DateTime('2016-06-30');

        $dr = DateRange::fromData($obj);

        $this->assertEquals(new DateTime('2015-09-01'), $dr->getStart());
        $this->assertEquals(new DateTime('2016-06-30'), $dr->getEnd());
    }

    /**
     * @test
     * @group time
     * @group daterange
     */
    public function should_create_from_data_strings()
    {
        $obj = new stdClass;
        $obj->start = '2015-09-01';
        $obj->end = '2016-06-30';

        $dr = DateRange::fromData($obj);

        $this->assertEquals(new DateTime('2015-09-01'), $dr->getStart());
        $this->assertEquals(new DateTime('2016-06-30'), $dr->getEnd());
    }

    /**
     * @test
     * @group time
     * @group daterange
     */
    public function should_make_infinite_from_null_range()
    {
        $obj = new stdClass();
        $obj->start = null;
        $obj->end = null;

        $dr = DateRange::fromData($obj);

        $this->assertEquals(new DateTime(DateRange::PAST), $dr->getStart());
        $this->assertEquals(new DateTime(DateRange::FUTURE), $dr->getEnd());
    }

    /**
     * @test
     * @group time
     * @group daterange
     */
    public function should_return_equal()
    {
        $dr1 = DateRange::fromIso8601('2015-09-01/2016-06-30');
        $dr2 = DateRange::fromIso8601('2015-09-01/2016-06-30');

        $this->assertTrue($dr1->equals($dr2));
        $this->assertTrue($dr2->equals($dr1));

        $ne1 = DateRange::fromIso8601('2015-09-01/2016-06-30');
        $ne2 = DateRange::fromIso8601('2014-09-01/2016-06-30');

        $this->assertFalse($ne1->equals($ne2));
        $this->assertFalse($ne2->equals($ne1));
    }

    /**
     * @test
     * @group time
     * @group daterange
     */
    public function should_include()
    {
        $dr = DateRange::fromIso8601('2015-09-01/2016-06-30');

        $this->assertTrue($dr->includes(new DateTime('2015-09-01')));
        $this->assertTrue($dr->includes(new DateTime('2016-05-31')));
        $this->assertFalse($dr->includes(new DateTime('2015-08-31')));
        $this->assertFalse($dr->includes(new DateTime('2016-07-01')));

        $this->assertTrue($dr->includes(DateRange::fromIso8601('2015-09-01/2016-05-31'))); //includes start date
        $this->assertTrue($dr->includes(DateRange::fromIso8601('2015-10-01/2016-05-31'))); //includes end date
        $this->assertFalse($dr->includes(DateRange::fromIso8601('2015-08-31/2016-04-01'))); //out of lower boundary
        $this->assertFalse($dr->includes(DateRange::fromIso8601('2016-04-01/2016-07-01'))); //out of higher boundary

        $this->setExpectedException('InvalidArgumentException');
        $dr->includes(new stdClass());
    }

    /**
     * @test
     * @group time
     * @group daterange
     */
    public function should_convert_to_ISO8601_string()
    {
        $dr = new DateRange(new DateTime('2015-09-01'), new DateTime('2016-06-30'));

        $this->assertEquals(
            '2015-09-01/2016-06-30',
            (string)$dr
        );

        $this->assertEquals(
            '2015-09-01/2016-06-30',
            $dr->__toString()
        );
    }

    /**
     * @test
     * @group time
     * @group daterange
     */
    public function testIsFutureIsPastIsInfinite()
    {
        $dr1 = new DateRange(
            new DateTime('2006-09-06'),
            new DateTime('2006-09-15')
        );
        $dr2 = new DateRange(
            new DateTime(DateRange::PAST),
            new DateTime('2006-09-15')
        );
        $dr3 = new DateRange(
            new DateTime('2006-09-06'),
            new DateTime(DateRange::FUTURE)
        );
        $dr4 = new DateRange(
            new DateTime(DateRange::PAST),
            new DateTime(DateRange::FUTURE)
        );
        $this->assertFalse($dr1->isPast(), $dr1->toString());
        $this->assertFalse($dr1->isFuture(), $dr1->toString());
        $this->assertFalse($dr1->isInfinite(), $dr1->toString());
        $this->assertTrue($dr2->isPast(), $dr2->toString());
        $this->assertFalse($dr2->isFuture(), $dr2->toString());
        $this->assertFalse($dr2->isInfinite(), $dr2->toString());
        $this->assertFalse($dr3->isPast(), $dr3->toString());
        $this->assertTrue($dr3->isFuture(), $dr3->toString());
        $this->assertFalse($dr3->isInfinite(), $dr3->toString());
        $this->assertTrue($dr4->isPast(), $dr4->toString());
        $this->assertTrue($dr4->isFuture(), $dr4->toString());
        $this->assertTrue($dr4->isInfinite(), $dr4->toString());
    }

    /**
     * @test
     * @group time
     * @group daterange
     */
    public function should_fail_on_invalid_data()
    {

        $this->setExpectedException(InvalidArgumentException::class);
        $fake = $this->faker->word;

        $dr = DateRange::fromData($fake);
    }

}
