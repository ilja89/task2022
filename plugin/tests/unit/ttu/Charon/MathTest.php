<?php

namespace tests\unit\capella;

use PHPUnit\Framework\TestCase;

use ttu\capella\Math;

class MathTest extends TestCase
{

    /**
     * @test
     * @covers Math::add
     */
    public function add_can_add()
    {
        $a = 4;
        $b = 10;
        $math = new Math;

        $result = $math->add($a, $b);

        $this->assertEquals(14, $result);
    }
}