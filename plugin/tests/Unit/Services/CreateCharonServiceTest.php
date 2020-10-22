<?php

namespace Tests\Unit\Services;

use Illuminate\Http\Request;
use Mockery as m;
use Tests\TestCase;
use TTU\Charon\Models\Charon;
use TTU\Charon\Services\CreateCharonService;
use TTU\Charon\Services\DeadlineService;
use TTU\Charon\Services\GrademapService;
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
        $this->markTestSkipped('Out of date, needs attention');

        $courseId = 1;
        $charon = m::mock(Charon::class)->makePartial();
        $charon->name = 'Testname';

        $createCharonService = new CreateCharonService(
            m::mock(GradebookService::class)
                ->shouldReceive('addGradeCategory')->with($courseId, $charon->name)->once()
                ->getMock(),
            m::mock(GrademapService::class),
            m::mock(DeadlineService::class)
        );

        $createCharonService->addCategoryForCharon($charon, $courseId);
    }

    public function testSaveGrademapsDoesSave()
    {
        $this->markTestSkipped('Out of date, needs attention');

        $grademap1 = [ 'name' => 'test' ];
        $grademap2 = [ 'name' => 'style' ];
        $grademaps = [ 1 => $grademap1, 101 => $grademap2 ];
        $charon = m::mock(Charon::class)->makePartial();
        $charon->name = 'test';
        $request = new \StdClass;
        $request->course = 1;
        $request->grademaps = $grademaps;

        $createCharonService = new CreateCharonService(
            m::mock(GradebookService::class),
            m::mock(GrademapService::class)
                ->shouldReceive('createGrademapWithGradeItem')->once()->with($charon, 1, 1, $grademap1)
                ->shouldReceive('createGrademapWithGradeItem')->once()->with($charon, 101, 1, $grademap2)
                ->shouldReceive('createGrademapWithGradeItem')->never()
                ->getMock(),
            m::mock(DeadlineService::class)
        );
        $createCharonService->saveGrademapsFromRequest($request, $charon);
    }

    public function testSavesDeadlines()
    {
        $this->markTestSkipped('Out of date, needs attention');

        $deadline1 = [ 'time' => 'now' ];
        $deadline2 = [ 'time' => 'soon' ];
        $deadlines = [ $deadline1, $deadline2 ];
        $charon = m::mock(Charon::class)->makePartial();
        $charon->name = 'test';
        $request = m::mock(Request::class);
        $request->deadlines = $deadlines;

        $createCharonService = new CreateCharonService(
            m::mock(GradebookService::class),
            m::mock(GrademapService::class),
            m::mock(DeadlineService::class)
                ->shouldReceive('createDeadline')->once()->with($charon, $deadline1)
                ->shouldReceive('createDeadline')->once()->with($charon, $deadline2)
                ->shouldReceive('createDeadline')->never()
                ->getMock()
        );

        $createCharonService->saveDeadlinesFromRequest($request, $charon);
    }

    public function testSaveDeadlinesNoDeadlines()
    {
        $this->markTestSkipped('Out of date, needs attention');

        $request = m::mock(Request::class);
        $request->deadlines = null;

        $createCharonService = new CreateCharonService(
            m::mock(GradebookService::class),
            m::mock(GrademapService::class),
            m::mock(DeadlineService::class)
             ->shouldReceive('createDeadline')->never()
             ->getMock()
        );

        $createCharonService->saveDeadlinesFromRequest($request, new Charon);
    }
}
