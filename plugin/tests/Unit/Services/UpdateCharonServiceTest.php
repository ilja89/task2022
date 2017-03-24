<?php

namespace Tests\Unit\Services;

use Illuminate\Http\Request;
use Mockery as m;
use Tests\TestCase;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Deadline;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Repositories\DeadlinesRepository;
use TTU\Charon\Services\DeadlineService;
use TTU\Charon\Services\GrademapService;
use TTU\Charon\Services\UpdateCharonService;
use Zeizig\Moodle\Models\GradeItem;
use Zeizig\Moodle\Services\GradebookService;

class UpdateCharonServiceTest extends TestCase
{
    public function testUpdatesGrademaps()
    {
        $request = m::mock(Request::class);
        $request->course = 1;
        $request->grademaps = [
            1 => [
                'grademap_name' => 'new name 1',
                'max_points' => 100,
                'id_number' => 'idnumber'
            ],
            101 => [
                'grademap_name' => 'new name 2',
                'max_points' => 100,
                'id_number' => 'idnumber'
            ],
        ];
        $grademap1 = m::mock('Grademap1')->shouldReceive('save')->getMock();
        $grademap1->grade_type_code = 1;
        $grademap1->name = 'old name 1';
        $grademap1->grade_item_id = 1;
        $grademap2 = m::mock('Grademap2')->shouldReceive('save')->getMock();
        $grademap2->grade_type_code = 101;
        $grademap2->name = 'old name 2';
        $grademap2->grade_item_id = 2;

        $charon = m::mock(Charon::class)->makePartial();
        $charon->grademaps = [$grademap1, $grademap2];

        $gradebookService = m::mock(GradebookService::class)
            ->shouldReceive('updateGradeItem')->twice()
            ->shouldReceive('createGrademapWithGradeItem')->never()
            ->getMock();

        $updateCharonService = new UpdateCharonService(
            m::mock(GrademapService::class),
            $gradebookService,
            m::mock(DeadlineService::class),
            m::mock(DeadlinesRepository::class)
        );

        $updateCharonService->updateGrademaps($request, $charon);

        $this->assertEquals('new name 1', $grademap1->name);
        $this->assertEquals('new name 2', $grademap2->name);
    }

    public function testUpdateGrademapsDeletesGrademap()
    {
        $request = m::mock(Request::class);
        $request->grademaps = [];
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
            m::mock(DeadlinesRepository::class)
        );

        $updateCharonService->updateGrademaps($request, $charon);
    }

    public function testUpdatesDeadlines()
    {
        $charon = m::mock(Charon::class)->makePartial();
        $charon->id = 1;
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
                ->getMock()
        );

        $updateCharonService->updateDeadlines($request, $charon);
    }

    public function testUpdatesCategory()
    {
        $charon = m::mock(Charon::class)->makePartial();
        $charon->category_id = 1;
        $request = ['calculation_formula' => '=[[test]] * [[style]]', 'max_score' => 1];
        $gradeItem = m::mock(GradeItem::class)
            ->shouldReceive('save')->once()
            ->getMock()->makePartial();

        $updateCharonService = new UpdateCharonService(
            m::mock(GrademapService::class),
            m::mock(GradebookService::class)
                ->shouldReceive('getGradeItemByCategoryId')->with($charon->category_id)->andReturn($gradeItem)
                ->getMock(),
            m::mock(DeadlineService::class),
            m::mock(DeadlinesRepository::class)
        );

        $updateCharonService->updateCategoryCalculationAndMaxScore($charon, $request);

        $this->assertEquals($request['calculation_formula'], $gradeItem->calculation);
        $this->assertEquals($request['max_score'], $gradeItem->grademax);
    }
}
