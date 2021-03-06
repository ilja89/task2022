<?php

namespace Tests\Unit\Dto;

use TTU\Charon\Dto\AreteRequestDto;
use PHPUnit\Framework\TestCase;

class AreteRequestDtoTest extends TestCase
{

    public function testToArray()
    {
        $dto = (new AreteRequestDto())
            ->setSystemExtra('first,second')
            ->setTestingPlatform('')
            ->setHash('value');

        $this->assertEquals(
            ['systemExtra' => ['first', 'second'], 'hash' => 'value'],
            $dto->toArray()
        );
    }

}
