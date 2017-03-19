<?php

namespace Tests;

use \Mockery as m;

class MockingTest extends TestCase
{
    protected function getNewMock($class, $originalArgs = [], $constructorArgs = [], $methodReturns = [])
    {
        foreach ($originalArgs as $index => $arg) {
            if ($constructorArgs[$index] === null) {
                $constructorArgs[$index] = m::mock($arg);
            }
        }

        if ($constructorArgs === [] && $methodReturns === []) {
            $mock = m::mock($class);
        } else if ($constructorArgs === []) {
            $mock = m::mock($class, $methodReturns);
        } else if ($methodReturns === []) {
            $mock = m::mock($class, $constructorArgs);
        } else {
            $mock = m::mock($class, $methodReturns, $constructorArgs);
        }

        return $mock;
    }
}
