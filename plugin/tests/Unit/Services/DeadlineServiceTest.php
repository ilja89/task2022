<?php

namespace Tests\Unit\Services;

use Carbon\Carbon;
use Mockery as m;
use Tests\TestCase;
use TTU\Charon\Models\Charon;
use TTU\Charon\Services\DeadlineService;

class DeadlineServiceTest extends TestCase
{
    protected $deadlineService;

    public function setUp()
    {
        $this->deadlineService = new DeadlineService;
    }


    public function testCreatesDeadlinesWithCorrectDeadlines()
    {
        $charon = m::mock(Charon::class);
        $deadlineArray = [
            'deadline_time' => Carbon::now()->addHour()->format('d-m-Y H:i'),
            'percentage' => 50
        ];
    }
}
