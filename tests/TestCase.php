<?php

class TestCase extends Laravel\Lumen\Testing\TestCase
{
    /** @var Faker\Generator */
    protected $faker;

    public function setUp()
    {
        parent::setUp();
        $this->faker = Faker\Factory::create('nl_BE');
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }
}
