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

    public function mock($class)
    {
        $mock = Mockery::mock($class);

        $this->app->instance($class, $mock);

        return $mock;
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
