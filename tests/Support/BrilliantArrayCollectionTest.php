<?php
use App\Support\BrilliantArrayCollection;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 3/08/16
 * Time: 20:57
 */
class BrilliantArrayCollectionTest extends TestCase
{
    /**
     * @test
     * @group collection
     */
    public function should_make_average()
    {
        $collection = $this->getCollection();


        $this->assertEquals(55, $collection->average('score'));

    }
    /**
     * @test
     * @group collection
     */
    public function should_get_median()
    {
        $collection = $this->getCollection();

        $this->assertEquals(55, $collection->median('score'));
    }
    /**
     * @test
     * @group collection
     */
    public function should_get_sdRange()
    {
        $collection = $this->getCollection();


        $this->assertGreaterThan(28, $collection->standardDeviation('score'));
        $this->assertLessThan(29, $collection->standardDeviation('score'));

    }
    /**
     * @test
     * @group collection
     */
    public function should_exclude_outlier()
    {
        $collection = $this->getCollection();

        $m = $collection->median('score');
        $sd = $collection->standardDeviation('score');

        $newCollection = $collection->excludeOutlier($m, $sd, 'score');

        $this->assertCount(6, $newCollection);
        $this->assertEquals(30, $newCollection->get(2)->score);
        $this->assertEquals(80, $newCollection->get(7)->score);
    }


    private function getCollection()
    {
        $collection = new BrilliantArrayCollection;

        foreach (range(1, 10) as $index) {
            $obj = new stdClass();
            $obj->score = $index * 10;
            $collection->add($obj);
        }
        return $collection;
    }
}
