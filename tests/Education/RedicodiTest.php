<?php
use App\Domain\Model\Education\Redicodi;

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 1/09/16
 * Time: 23:33
 */
class RedicodiTest extends TestCase
{
    /**
     * @test
     * @group redicodi
     */
    public function should_create_new() {
        foreach (Redicodi::values() as $value) {
            $redicodi = new Redicodi((string)$value);
            $this->assertEquals($value, $redicodi);
        }
    }

}
