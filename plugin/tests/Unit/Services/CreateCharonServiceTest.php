<?php

namespace Tests\Unit\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Mockery as m;
use Tests\TestCase;
use TTU\Charon\Models\Charon;
use TTU\Charon\Services\CharonDefenseLabService;
use TTU\Charon\Services\CreateCharonService;
use TTU\Charon\Services\DeadlineService;
use TTU\Charon\Services\GrademapService;
use Zeizig\Moodle\Services\CalendarService;
use Zeizig\Moodle\Services\GradebookService;

/**
 * Class CreateCharonServiceTest.
 *
 * @package Tests\Unit\Services
 */
class CreateCharonServiceTest extends TestCase
{
    public function testAddsCategoryForCharon()
    {
        $courseId = 1;
        $charon = m::mock(Charon::class)->makePartial();
        $charon->name = 'Testname';

        $createCharonService = new CreateCharonService(
            m::mock(GradebookService::class)
                ->shouldReceive('addGradeCategory')->with($courseId, $charon->name)->once()
                ->getMock(),
            m::mock(GrademapService::class),
            m::mock(DeadlineService::class),
            m::mock(CharonDefenseLabService::class),
            m::mock(CalendarService::class)
        );

        $createCharonService->addCategoryForCharon($charon, $courseId);
    }

    public function testSaveGrademapsDoesSave()
    {
        $grademap1 = [ 'name' => 'test' ];
        $grademap2 = [ 'name' => 'style' ];
        $grademaps = [ 1 => $grademap1, 101 => $grademap2 ];
        $charon = m::mock(Charon::class)->makePartial();
        $charon->name = 'test';
        $request = m::mock(\StdClass::class);
        $request->course = 1;
        $request->grademaps = $grademaps;

        $createCharonService = new CreateCharonService(
            m::mock(GradebookService::class),
            m::mock(GrademapService::class)
                ->shouldReceive('createGrademapWithGradeItem')->once()->with($charon, 1, 1, $grademap1)
                ->shouldReceive('createGrademapWithGradeItem')->once()->with($charon, 101, 1, $grademap2)
                ->shouldReceive('createGrademapWithGradeItem')->never()
                ->getMock(),
            m::mock(DeadlineService::class),
            m::mock(CharonDefenseLabService::class),
            m::mock(CalendarService::class)
        );

        $request->shouldReceive('has')->with('grademaps')->once()->andReturn(true);

        $request->shouldReceive('input')->with('grademaps')->once()->andReturn($grademaps);

        $createCharonService->saveGrademapsFromRequest($request, $charon);
    }

    public function testSaveGrademapsDoesNotSave()
    {
        $charon = m::mock(Charon::class)->makePartial();
        $charon->name = 'test';
        $request = m::mock(\StdClass::class);
        $request->course = 1;

        $createCharonService = new CreateCharonService(
            m::mock(GradebookService::class),
            m::mock(GrademapService::class),
            m::mock(DeadlineService::class),
            m::mock(CharonDefenseLabService::class),
            m::mock(CalendarService::class)
        );

        $request->shouldReceive('has')->with('grademaps')->once()->andReturn(false);

        $createCharonService->saveGrademapsFromRequest($request, $charon);
    }

    public function testSavesDeadlines()
    {
        $deadline1 = [ 'time' => 'now' ];
        $deadline2 = [ 'time' => 'soon' ];
        $deadlines = [ $deadline1, $deadline2 ];
        $charon = m::mock(Charon::class, ['load' => null])->makePartial();
        $charon->name = 'test';
        $charon->deadlines = Collection::make([]);
        $request = m::mock(Request::class);
        $request->deadlines = $deadlines;

        $createCharonService = new CreateCharonService(
            m::mock(GradebookService::class),
            m::mock(GrademapService::class),
            m::mock(DeadlineService::class)
                ->shouldReceive('createDeadline')->once()->with($charon, $deadline1)
                ->shouldReceive('createDeadline')->once()->with($charon, $deadline2)
                ->shouldReceive('createDeadline')->never()
                ->getMock(),
            m::mock(CharonDefenseLabService::class),
            m::mock(CalendarService::class)
        );

        $createCharonService->saveDeadlinesFromRequest($request, $charon, "99");
    }

    public function testSaveDeadlinesNoDeadlines()
    {
        $charon = m::mock(Charon::class, ['load' => null])->makePartial();
        $charon->name = 'test';
        $charon->deadlines = Collection::make([]);
        $request = m::mock(Request::class);
        $request->deadlines = null;

        $createCharonService = new CreateCharonService(
            m::mock(GradebookService::class),
            m::mock(GrademapService::class),
            m::mock(DeadlineService::class)
             ->shouldReceive('createDeadline')->never()
             ->getMock(),
            m::mock(CharonDefenseLabService::class),
            m::mock(CalendarService::class)
        );


        $createCharonService->saveDeadlinesFromRequest($request, $charon, 99);
    }
}
