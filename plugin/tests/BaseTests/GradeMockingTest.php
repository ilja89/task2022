<?php

namespace Tests\BaseTests;

use Tests\MockingTest;
use TTU\Charon\Models\Grademap;
use Zeizig\Moodle\Models\GradeGrade;
use Zeizig\Moodle\Models\GradeItem;

class GradeMockingTest extends MockingTest
{

    protected function getMockResult($calculatedResult, $previousResult = 0)
    {
        $gradeGrade = new GradeGrade;
        $gradeGrade->finalgrade = $previousResult;
        $gradeItem = $this->getNewMock(GradeItem::class, [], [], ['gradesForUser' => $gradeGrade]);
        $grademap = new Grademap;
        $grademap->gradeItem = $gradeItem;
        $result = $this->getNewMock('Result', [], [], ['getGrademap' => $grademap]);
        $result->calculated_result = $calculatedResult;

        return $result;
    }

    protected function getMockWorseResults()
    {
        $results = [];
        $results[] = $this->getMockResult(0.5, 1);
        $results[] = $this->getMockResult(0, 1);
        $results[] = $this->getMockResult(0, 1);

        return $results;
    }
}
