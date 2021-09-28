<?php

namespace Tests\Traits;

use Mockery;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use Zeizig\Moodle\Models\GradeGrade;
use Zeizig\Moodle\Models\GradeItem;

trait MocksSubmission
{
    protected function makeResult($calculatedResult, $previousResult = 0, $gradeTypeCode = 1)
    {
        $gradeGrade = new GradeGrade();
        $gradeGrade->finalgrade = $previousResult;

        $gradeItem = Mockery::mock(GradeItem::class, ['gradesForUser' => $gradeGrade]);

        $grademap = new Grademap();
        $grademap->gradeItem = $gradeItem;
        $grademap->grade_type_code = $gradeTypeCode;

        /** @var Result $result */
        $result = Mockery::mock('Result', ['getGrademap' => $grademap]);
        $result->calculated_result = $calculatedResult;
        $result->grade_type_code = $gradeTypeCode;

        return $result;
    }

    protected function makeSubmissionWithWorseResults($props = [])
    {
        $submission = new Submission();

        foreach ($props as $propName => $propVal) {
            $submission->$propName = $propVal;
        }

        $submission->results = collect([
            $this->makeResult(0.5, 1, 1),
            $this->makeResult(0, 1, 101),
            $this->makeResult(0, 1, 1001)
        ]);

        return $submission;
    }
}
