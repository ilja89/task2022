<?php

namespace Tests\BaseTests;

use Illuminate\Support\Collection;
use Tests\MockingTest;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\Submission;
use Zeizig\Moodle\Models\GradeGrade;
use Zeizig\Moodle\Models\GradeItem;

class GradeMockingTest extends MockingTest
{

    protected function getMockResult($calculatedResult, $previousResult = 0, $gradeTypeCode = 1)
    {
        $gradeGrade                = new GradeGrade;
        $gradeGrade->finalgrade    = $previousResult;
        $gradeItem                 = $this->getNewMock(GradeItem::class, [], [], ['gradesForUser' => $gradeGrade]);
        $grademap                  = new Grademap;
        $grademap->gradeItem       = $gradeItem;
        $grademap->grade_type_code = $gradeTypeCode;
        $result                    = $this->getNewMock('Result', [], [], ['getGrademap' => $grademap]);
        $result->calculated_result = $calculatedResult;
        $result->grade_type_code   = $gradeTypeCode;

        return $result;
    }

    protected function getMockWorseResults()
    {
        $results   = [];
        $results[] = $this->getMockResult(0.5, 1, 1);
        $results[] = $this->getMockResult(0, 1, 101);
        $results[] = $this->getMockResult(0, 1, 1001);

        return Collection::make($results);
    }

    protected function getMockWorseSubmission($props = [])
    {
        $submission = new Submission;

        foreach ($props as $propName => $propVal) {
            $submission->$propName = $propVal;
        }
        $submission->results = $this->getMockWorseResults();

        return $submission;
    }
}
