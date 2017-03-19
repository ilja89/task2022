<?php

namespace Tests;

class MockingTest extends TestCase
{
    protected function getNewMock($class, $originalArgs = [], $constructorArgs = [], $methodReturns = [])
    {
        foreach ($originalArgs as $index => $arg) {
            if ($constructorArgs[$index] === null) {
                $constructorArgs[$index] = $this->getMockBuilder($arg)->disableOriginalConstructor()->getMock();
            }
        }

        $mock = $this->getMockBuilder($class)
                     ->setConstructorArgs($constructorArgs)
                     ->setMethods(array_keys($methodReturns))
                     ->getMock();

        foreach ($methodReturns as $method => $return) {
            $mock->method($method)->willReturn($return);
        }

        return $mock;
    }
}
