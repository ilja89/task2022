<?php

namespace Tests\Unit\Services;

use Mockery as m;
use Tests\TestCase;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Services\GrademapService;
use Zeizig\Moodle\Models\GradeItem;
use Zeizig\Moodle\Services\GradebookService;

class GrademapServiceTest extends TestCase
{
    public function testLinksGrademapsAndGradeItemsCorrectly()
    {
        $grademap1 = m::mock(Grademap::class)->shouldReceive('save')->once()->getMock()->makePartial();
        $grademap1->grade_type_code = 1;
        $grademap2 = m::mock(Grademap::class)->shouldReceive('save')->once()->getMock()->makePartial();
        $grademap2->grade_type_code = 101;
        $grademap3 = m::mock(Grademap::class)->shouldReceive('save')->once()->getMock()->makePartial();
        $grademap3->grade_type_code = 1001;
        $grademaps = [$grademap1, $grademap2, $grademap3];

        $gradeItems = [
            $this->getGradeItem(1, 1),
            $this->getGradeItem(2, 101),
            $this->getGradeItem(3, 1001),
        ];
        $charon = m::mock(Charon::class, ['gradeItems' => $gradeItems])->makePartial();
        $charon->grademaps = $grademaps;

        $grademapService = new GrademapService(m::mock(GradebookService::class));

        $grademapService->linkGrademapsAndGradeItems($charon);

        $this->assertEquals(1, $grademap1->grade_item_id);
        $this->assertEquals(2, $grademap2->grade_item_id);
        $this->assertEquals(3, $grademap3->grade_item_id);
    }

    public function testDeletesGrademapAndGradeItem()
    {
        $grademapService = new GrademapService(m::mock(GradebookService::class));
        $gradeItem = m::mock(GradeItem::class)
            ->shouldReceive('delete')->once()
            ->getMock()->makePartial();
        $grademap = m::mock(Grademap::class)
            ->shouldReceive('delete')->once()
            ->getMock()->makePartial();
        $grademap->gradeItem = $gradeItem;

        $grademapService->deleteGrademap($grademap);
    }

    public function testCreatesGrademapWithGradeItem()
    {
        $charon = m::mock(Charon::class, ['grademaps' => m::mock('grademaps')->shouldReceive('save')->once()->getMock()])
            ->makePartial();
        $charon->id = 1;
        $grademap = [
            'grademap_name' => 'test grademap',
            'max_points' => 100,
            'id_number' => 'id number'
        ];

        $grademapService = new GrademapService(
            m::mock(GradebookService::class)
                ->shouldReceive('addGradeItem')
                ->with($charon->id, 1, 1, $grademap['grademap_name'], $grademap['max_points'], $grademap['id_number'])
                ->once()
                ->getMock()
        );

        $grademapService->createGrademapWithGradeItem($charon, 1, 1, $grademap);
    }

    private function getGradeItem($id, $itemNumber)
    {
        $gradeItem = m::mock(GradeItem::class)->makePartial();
        $gradeItem->itemnumber = $itemNumber;
        $gradeItem->id = $id;

        return $gradeItem;
    }
}
