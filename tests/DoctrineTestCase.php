<?php

/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 22/06/16
 * Time: 21:13
 */
abstract class DoctrineTestCase extends TestCase
{
    protected $em;

    public function setUp()
    {
        parent::setUp();

        $this->prepareForTests();

        $this->em->beginTransaction();
    }

    public function tearDown()
    {
        if ($this->em) {
            $this->em->rollback();
        }
    }

    public function prepareForTests()
    {
        $this->em = $this->app->make(\Doctrine\ORM\EntityManager::class);
    }
}

