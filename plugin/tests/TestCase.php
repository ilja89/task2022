<?php

namespace Tests;

use Faker\Generator;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Faker\Factory;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /** @var Generator */
    protected $faker;

    /**
     * TestCase constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->faker = Factory::create();
    }
}
