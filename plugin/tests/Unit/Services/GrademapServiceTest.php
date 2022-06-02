<?php

namespace Tests\Unit\Services;

use Mockery;
use Mockery\Mock;
use Tests\TestCase;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\GradeItemRepository;
use TTU\Charon\Services\GrademapService;
use Zeizig\Moodle\Models\GradeCategory;
use Zeizig\Moodle\Models\GradeGrade;
use Zeizig\Moodle\Models\GradeItem;
use Zeizig\Moodle\Services\GradebookService;

class GrademapServiceTest extends TestCase
{
    /** @var Mock|GradebookService */
    private $gradebookService;

    /** @var Mock|GradeItemRepository */
    private $gradeItemRepository;

    /** @var GrademapService */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new GrademapService(
            $this->gradebookService = Mockery::mock(GradebookService::class),
            $this->gradeItemRepository = Mockery::mock(GradeItemRepository::class)
        );
    }

    public function testLinksGrademapsAndGradeItemsCorrectly()
    {
        /** @var Grademap $grademap1 */
        $grademap1 = Mockery::mock(Grademap::class)->shouldReceive('save')->once()->getMock()->makePartial();
        $grademap1->grade_type_code = 1;

        /** @var Grademap $grademap2 */
        $grademap2 = Mockery::mock(Grademap::class)->shouldReceive('save')->once()->getMock()->makePartial();
        $grademap2->grade_type_code = 101;

        /** @var Grademap $grademap3 */
        $grademap3 = Mockery::mock(Grademap::class)->shouldReceive('save')->once()->getMock()->makePartial();
        $grademap3->grade_type_code = 1001;

        $grademaps = [$grademap1, $grademap2, $grademap3];
        $gradeItems = [
            $this->getGradeItem(1, 1),
            $this->getGradeItem(2, 101),
            $this->getGradeItem(3, 1001),
        ];

        /** @var Charon $charon */
        $charon = Mockery::mock(Charon::class, ['gradeItems' => $gradeItems])->makePartial();
        $charon->grademaps = $grademaps;

        $this->service->linkGrademapsAndGradeItems($charon);

        $this->assertEquals(1, $grademap1->grade_item_id);
        $this->assertEquals(2, $grademap2->grade_item_id);
        $this->assertEquals(3, $grademap3->grade_item_id);
    }

    public function testDeletesGrademapAndGradeItem()
    {
        /** @var GradeItem $gradeItem */
        $gradeItem = Mockery::mock(GradeItem::class)
            ->shouldReceive('delete')->once()
            ->getMock()->makePartial();

        /** @var Grademap $grademap */
        $grademap = Mockery::mock(Grademap::class)
            ->shouldReceive('delete')->once()
            ->getMock()->makePartial();

        $grademap->gradeItem = $gradeItem;

        $this->service->deleteGrademap($grademap);
    }

    public function testCreatesGrademapWithGradeItem()
    {
        /** @var Charon $charon */
        $charon = Mockery::mock(Charon::class, [
            'grademaps' => Mockery::mock('grademaps')->shouldReceive('save')->once()->getMock()
        ])->makePartial();

        $charon->id = 1;

        $grademap = [
            'grademap_name' => 'test grademap',
            'max_points' => 100,
            'id_number' => 'id number'
        ];

        $this->gradebookService
            ->shouldReceive('addGradeItem')
            ->with($charon->id, 1, 1, $grademap['grademap_name'], $grademap['max_points'], $grademap['id_number'])
            ->once();

        $this->service->createGrademapWithGradeItem($charon, 1, 1, $grademap);
    }

    public function testFindFormulaParamsUsesOnlyResultsIfNoExtraMatch()
    {
        $grademap = new Grademap();
        $grademap->gradeItem = $this->getGradeItem(3, 5);

        /** @var Result $result */
        $result = Mockery::mock(Result::class, ['getGrademap' => $grademap])->makePartial();
        $result->calculated_result = 7;
        $result->user_id = 11;

        $params = $this->service->findFormulaParams('=##gi3##', collect([$result]), 11);

        $this->assertEquals(['gi3' => 7], $params);
    }

    public function testFindFormulaParamsAddsGradesFromNewMatches()
    {
        $grademap = new Grademap();
        $grademap->gradeItem = $this->getGradeItem(3, 5);

        /** @var Result $result */
        $result = Mockery::mock(Result::class, ['getGrademap' => $grademap])->makePartial();
        $result->calculated_result = 7;
        $result->user_id = 11;

        $this->gradeItemRepository
            ->shouldReceive('find')
            ->with(17)
            ->once()
            ->andReturnNull();

        $gradeItem19 = $this->getGradeItem(19, 19);
        $gradeItem19->shouldReceive('gradesForUser')
            ->with(11)
            ->once()
            ->andReturnNull();

        $this->gradeItemRepository
            ->shouldReceive('find')
            ->with(19)
            ->once()
            ->andReturn($gradeItem19);

        $grade = new GradeGrade();
        $grade->rawgrade = 29;

        $gradeItem23 = $this->getGradeItem(23, 23);
        $gradeItem23->shouldReceive('gradesForUser')
            ->with(11)
            ->once()
            ->andReturn($grade);

        $this->gradeItemRepository
            ->shouldReceive('find')
            ->with(23)
            ->once()
            ->andReturn($gradeItem23);

        $params = $this->service->findFormulaParams('=##gi3## + ##gi17## * (##gi19## + ##gi23##)', collect([$result]), 11);

        $this->assertEquals(['gi3' => 7, 'gi23' => 29], $params);

        $this->gradeItemRepository->shouldNotHaveReceived('find', [3]);
    }

    public function testFindFormulaParamsIgnoresGradesWithoutGrademap()
    {
        $userId = 99;
        $gradeItem1Id = 1;
        $gradeItem2Id = 2;

        $gradeItem1 = $this->getGradeItem($gradeItem1Id, 1);
        $grademap1  = Mockery::mock(Grademap::class, ['gradeItem' => $gradeItem1]);
        $grademap1->shouldReceive('getAttribute')->with('gradeItem')->times(2)->andReturn($gradeItem1);
        $gradeItem2 = $this->getGradeItem($gradeItem2Id, 2);
        $grademap2  = Mockery::mock(Grademap::class, ['gradeItem' => $gradeItem2]);
        $grademap2->shouldReceive('getAttribute')->with('gradeItem')->times(2)->andReturn($gradeItem2);

        $result1CalculatedResult = 1;
        $result2CalculatedResult = 2;

        $result1 = Mockery::mock(
            Result::class,
            ['getGrademap' => $grademap1, 'calculated_result' => $result1CalculatedResult, 'user_id' => $userId]
        );
        $result2 = Mockery::mock(
            Result::class,
            ['getGrademap' => $grademap2, 'calculated_result' => $result2CalculatedResult, 'user_id' => $userId]
        );
        $result3 = Mockery::mock(
            Result::class,
            ['getGrademap' => null, 'calculated_result' => 3, 'user_id' => $userId]
        );
        $result3->shouldReceive('getGrademap')->once()->andReturnNull();

        $result1->shouldReceive('getAttribute')->with('user_id')->once()->andReturn($userId);
        $result2->shouldReceive('getAttribute')->with('user_id')->once()->andReturn($userId);
        $result3->shouldReceive('getAttribute')->with('user_id')->once()->andReturn($userId);

        $result1->shouldReceive('getAttribute')->with('calculated_result')->once()->andReturn($result1CalculatedResult);
        $result2->shouldReceive('getAttribute')->with('calculated_result')->once()->andReturn($result2CalculatedResult);

        $results = collect([$result1, $result2, $result3]);

        $calculationFormula = '=##gi1## * ##gi2##';

        $params = $this->service->findFormulaParams($calculationFormula, $results, $userId);
        $this->assertEquals(['gi1' => 1, 'gi2' => 2], $params);
    }

    /**
     * @param int $id
     * @param $itemNumber
     *
     * @return GradeItem|Mock
     */
    private function getGradeItem(int $id, $itemNumber)
    {
        /** @var GradeItem $gradeItem */
        $gradeItem = Mockery::mock(GradeItem::class)->makePartial();
        $gradeItem->itemnumber = $itemNumber;
        $gradeItem->id = $id;

        return $gradeItem;
    }
}
