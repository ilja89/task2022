<?php

namespace Tests\Unit\Dto;

use TTU\Charon\Dto\AreteRequestDto;
use PHPUnit\Framework\TestCase;

class AreteRequestDtoTest extends TestCase
{

    public function testToArrayRemovesEmpty()
    {
        $dto = (new AreteRequestDto())
            ->setSystemExtra('first,second')
            ->setTestingPlatform('')
            ->setDockerContentRoot(null)
            ->setHash('value');

        $this->assertEquals(
            ['systemExtra' => ['first', 'second'], 'hash' => 'value'],
            $dto->toArray()
        );
    }

}
