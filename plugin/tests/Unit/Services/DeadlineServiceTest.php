<?php

namespace Tests\Unit\Services;

use Carbon\Carbon;
use Mockery as m;
use Tests\TestCase;
use TTU\Charon\Models\Charon;
use TTU\Charon\Services\DeadlineService;

class DeadlineServiceTest extends TestCase
{
    /** @var DeadlineService */
    protected $deadlineService;

    public function setUp(): void
    {
        $this->deadlineService = new DeadlineService;
    }


    public function testCreatesDeadlinesWithCorrectDeadlines()
    {
        $this->createApplication();
        \Config::set('app.timezone', 'Europe/Tallinn');

        $charon = m::mock(Charon::class,
            ['deadlines' => m::mock('deadlines')->shouldReceive('save')->once()->getMock()]
        );
        $deadlineArray = [
            'deadline_time' => Carbon::now()->addHour()->format('d-m-Y H:i'),
            'percentage' => 50,
            'group_id' => 2
        ];

        $this->deadlineService->createDeadline($charon, $deadlineArray);
    }

    public function testDoesNotCreateDeadlinesWhenIncorrect()
    {
        $charon = m::mock(Charon::class,
            ['deadlines' => m::mock('deadlines')->shouldReceive('save')->never()->getMock()]
        );
        $deadlineArray = [
            'deadline_time' => 'some incorrect value',
            'percentage' => 'Haha'
        ];

        $this->deadlineService->createDeadline($charon, $deadlineArray);
    }
}
