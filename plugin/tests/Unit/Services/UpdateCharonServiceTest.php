<?php

namespace Tests\Unit\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Mockery as m;
use Tests\TestCase;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Repositories\DeadlinesRepository;
use TTU\Charon\Services\CharonGradingService;
use TTU\Charon\Services\DeadlineService;
use TTU\Charon\Services\GrademapService;
use TTU\Charon\Services\UpdateCharonService;
use Zeizig\Moodle\Models\GradeItem;
use Zeizig\Moodle\Services\CalendarService;
use Zeizig\Moodle\Services\GradebookService;

class UpdateCharonServiceTest extends TestCase
{
    public function testUpdatesGrademaps()
    {
        $newGrademaps = [
            1 => [
                'grademap_name' => 'new name 1',
                'max_points' => 1,
                'id_number' => 'idnumber'
            ],
            101 => [
                'grademap_name' => 'new name 2',
                'max_points' => 1,
                'id_number' => 'idnumber'
            ],
        ];
        $grademap1 = m::mock('Grademap1')->shouldReceive('save')->getMock();
        $grademap1->grade_type_code = 1;
        $grademap1->name = 'old name 1';
        $grademap1->grade_item_id = 1;
        $grademap1->gradeItem = new \StdClass;
        $grademap1->gradeItem->grademax = 1;
        $grademap2 = m::mock('Grademap2')->shouldReceive('save')->getMock();
        $grademap2->grade_type_code = 101;
        $grademap2->name = 'old name 2';
        $grademap2->grade_item_id = 2;
        $grademap2->gradeItem = new \StdClass;
        $grademap2->gradeItem->grademax = 1;

        $charon = m::mock(Charon::class)->makePartial();
        $charon->course = 1;
        $charon->grademaps = [$grademap1, $grademap2];

        $gradebookService = m::mock(GradebookService::class)
            ->shouldReceive('updateGradeItem')->twice()
            ->shouldReceive('createGrademapWithGradeItem')->never()
            ->getMock();

        $updateCharonService = new UpdateCharonService(
            m::mock(GrademapService::class),
            $gradebookService,
            m::mock(DeadlineService::class),
            m::mock(DeadlinesRepository::class),
            m::mock(CharonGradingService::class),
            m::mock(CalendarService::class)
        );

        $updateCharonService->updateGrademaps($newGrademaps, $charon);

        $this->assertEquals('new name 1', $grademap1->name);
        $this->assertEquals('new name 2', $grademap2->name);
    }

    public function testUpdateGrademapsDeletesGrademap()
    {
        $newGrademaps = [];
        $grademap = m::mock(Grademap::class)->makePartial();
        $grademap->grade_type_code = 1;
        $charon = m::mock(Charon::class)->makePartial();
        $charon->grademaps = [$grademap];

        $updateCharonService = new UpdateCharonService(
            m::mock(GrademapService::class)
                ->shouldReceive('deleteGrademap')->with($grademap)->once()
                ->shouldReceive('createGrademapWithGradeItem')->never()
                ->getMock(),
            m::mock(GradebookService::class),
            m::mock(DeadlineService::class),
            m::mock(DeadlinesRepository::class),
            m::mock(CharonGradingService::class),
            m::mock(CalendarService::class)
        );

        $updateCharonService->updateGrademaps($newGrademaps, $charon);
    }

    public function testUpdatesDeadlines()
    {
        $charon = m::mock(Charon::class, ['load' => null])->makePartial();
        $charon->id = 1;
        $charon->deadlines = Collection::make([]);
        $deadline1 = m::mock(Deadline::class)->makePartial();
        $deadline2 = m::mock(Deadline::class)->makePartial();
        $request = m::mock(Request::class);
        $request->deadlines = [$deadline1, $deadline2];

        $updateCharonService = new UpdateCharonService(
            m::mock(GrademapService::class),
            m::mock(GradebookService::class),
            m::mock(DeadlineService::class)
                ->shouldReceive('createDeadline')->with($charon, $deadline1)->once()
                ->shouldReceive('createDeadline')->with($charon, $deadline2)->once()
                ->getMock(),
            m::mock(DeadlinesRepository::class)
                ->shouldReceive('deleteAllDeadlinesForCharon')->with($charon->id)->once()
                ->shouldReceive('deleteAllCalendarEventsForCharon')->with($charon->id)->once()
                ->getMock(),
            m::mock(CharonGradingService::class),
            m::mock(CalendarService::class)
        );

        $updateCharonService->updateDeadlines($request, $charon, "99");
    }

    public function testUpdatesCategory()
    {
        $charon = m::mock(Charon::class)->makePartial();
        $charon->category_id = 1;
        $request = m::mock(\StdClass::class);
        $request->calculation_formula = '=[[test]] * [[style]]';
        $request->max_score = 1;
        $gradeItem = m::mock(GradeItem::class);

        $updateCharonService = new UpdateCharonService(
            m::mock(GrademapService::class),
            m::mock(GradebookService::class)
                ->shouldReceive('getGradeItemByCategoryId')->with($charon->category_id)->andReturn($gradeItem)
                ->getMock(),
            m::mock(DeadlineService::class),
            m::mock(DeadlinesRepository::class),
            m::mock(CharonGradingService::class),
            m::mock(CalendarService::class)
        );

        $request->shouldReceive('has')->with('max_score')->once()->andReturn(false);

        $updateCharonService->updateCategoryCalculationAndMaxScore($charon, $request);
    }
}
