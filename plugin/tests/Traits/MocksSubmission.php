<?php

namespace Tests\Traits;

use Illuminate\Support\Collection;
use \Mockery as m;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\Submission;
use Zeizig\Moodle\Models\GradeGrade;
use Zeizig\Moodle\Models\GradeItem;

trait MocksSubmission
{
    protected function getMockResult($calculatedResult, $previousResult = 0, $gradeTypeCode = 1)
    {
        $gradeGrade                = new GradeGrade;
        $gradeGrade->finalgrade    = $previousResult;
        $gradeItem                 = m::mock(GradeItem::class, ['gradesForUser' => $gradeGrade]);
        $grademap                  = new Grademap;
        $grademap->gradeItem       = $gradeItem;
        $grademap->grade_type_code = $gradeTypeCode;
        $result                    = m::mock('Result', ['getGrademap' => $grademap]);
        $result->calculated_result = $calculatedResult;
        $result->grade_type_code   = $gradeTypeCode;

        return $result;
    }

    protected function getMockResultWithoutGrademap($calculatedResult, $gradeTypeCode = 1)
    {
        $result                    = m::mock('Result', ['getGrademap' => null]);
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

    protected function getMockBetterSubmission($props = [])
    {
        $submission = new Submission;

        foreach ($props as $propName => $propVal) {
            $submission->$propName = $propVal;
        }
        $submission->results = Collection::make([
            $this->getMockResult(1, 0.5, 1),
            $this->getMockResult(1, 1, 101)
        ]);

        return $submission;
    }
}
