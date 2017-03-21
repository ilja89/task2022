<?php

namespace Tests\Unit\Services;

use Illuminate\Support\Collection;
use Mockery as m;
use Tests\TestCase;
use TTU\Charon\Helpers\RequestHandler;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GradeType;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Models\SubmissionFile;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Services\CharonGradingService;
use TTU\Charon\Services\SubmissionService;
use Zeizig\Moodle\Services\GradebookService;

class SubmissionServiceTest extends TestCase
{
    public function testSavesSubmissionWithResultsAndFiles()
    {
        $request = ['files' => ['file 1', 'file 2'], 'results' => ['result 1', 'result 2']];  // Not important content as it will only loop through the contents
        $charon = m::mock('Charon');
        $charon->grademaps = [];
        $submission = m::mock(Submission::class)->shouldReceive('save')->once()->getMock()->makePartial();
        $submission->id = 1;
        $submission->charon = $charon;
        $file1 = m::mock(SubmissionFile::class)->shouldReceive('save')->once()->getMock()->makePartial();
        $file2 = m::mock(SubmissionFile::class)->shouldReceive('save')->once()->getMock()->makePartial();
        $result1 = m::mock(Result::class)->shouldReceive('save')->once()->getMock()->makePartial();
        $result2 = m::mock(Result::class)->shouldReceive('save')->once()->getMock()->makePartial();

        $requestHandler = m::mock(RequestHandler::class)
            ->shouldReceive('getSubmissionFromRequest')->with($request)->andReturn($submission)
            ->shouldReceive('getResultFromRequest')->with(1, 'result 1')->andReturn($result1)
            ->shouldReceive('getResultFromRequest')->with(1, 'result 2')->andReturn($result2)
            ->shouldReceive('getFileFromRequest')->with(1, 'file 1')->andReturn($file1)
            ->shouldReceive('getFileFromRequest')->with(1, 'file 2')->andReturn($file2)
            ->getMock();

        $submissionService = new SubmissionService(
            m::mock(GradebookService::class),
            m::mock(CharonGradingService::class),
            $requestHandler,
            m::mock(SubmissionsRepository::class)
        );

        $result = $submissionService->saveSubmission($request);

        $this->assertEquals($submission, $result);
    }

    public function testCreatesCustomGradesOnSave()
    {
        $grademap = m::mock('Grademap');
        $grademap->gradeType = m::mock(GradeType::class, ['isCustomGrade' => true]);
        $grademap->grade_type_code = 1001;

        $charon = m::mock('Charon');
        $charon->grademaps = [$grademap];
        $submission = m::mock(Submission::class, ['save' => null])->makePartial();
        $submission->id = 1;
        $submission->charon = $charon;

        $submissionsRepository = m::mock(SubmissionsRepository::class)
            ->shouldReceive('saveNewEmptyResult')
            ->with($submission->id, $grademap->grade_type_code, m::any())
            ->once()
            ->getMock();

        $submissionService = new SubmissionService(
            m::mock(GradebookService::class),
            m::mock(CharonGradingService::class),
            m::mock(RequestHandler::class, [
                'getSubmissionFromRequest' => $submission
            ]),
            $submissionsRepository
        );

        $submissionService->saveSubmission(['results' => [], 'files' => []]);
    }

    public function testUpdateSubmissionCalculatedResults()
    {
        $result1 = m::mock(Result::class)
            ->shouldReceive('save')
            ->once()
            ->getMock()->makePartial();
        $result1->id = 1;
        $result1->calculated_result = 50;
        $result2 = m::mock(Result::class)
                    ->shouldReceive('save')
                    ->once()
                    ->getMock()->makePartial();
        $result2->id = 2;
        $result2->calculated_result = 0;
        $submission = m::mock(Submission::class)->makePartial();
        $submission->results = Collection::make([$result1, $result2]);
        $newResults = [
            [ 'id' => 1, 'calculated_result' => 100 ],
            [ 'id' => 2, 'calculated_result' => 80 ],
        ];

        $submissionService = new SubmissionService(
            m::mock(GradebookService::class),
            m::mock(CharonGradingService::class)
                ->shouldReceive('updateGradeIfApplicable')->with($submission, true)
                ->shouldReceive('confirmSubmission')->with($submission)
                ->getMock(),
            m::mock(RequestHandler::class),
            m::mock(SubmissionsRepository::class)
        );

        $submissionService->updateSubmissionCalculatedResults($submission, $newResults);

        $this->assertEquals(100, $result1->calculated_result);
        $this->assertEquals(80, $result2->calculated_result);
    }

    public function testAddsNewSubmission()
    {
        $submission = m::mock('Submission');
        $submission->id = 1;

        $charon = m::mock(Charon::class, [
            'submissions' => m::mock('Submissions')
                ->shouldReceive('create')->andReturn($submission)
                ->getMock()
        ])->makePartial();
        $grademap1 = m::mock('grademap1');
        $grademap1->grade_type_code = 1;
        $grademap2 = m::mock('grademap2');
        $grademap2->grade_type_code = 101;
        $charon->grademaps = [$grademap1, $grademap2];

        $submissionsRepository = m::mock(SubmissionsRepository::class)
            ->shouldReceive('saveNewEmptyResult')->once()->with(1, 1, '')
            ->shouldReceive('saveNewEmptyResult')->once()->with(1, 101, '')
            ->getMock();

        $submissionService = new SubmissionService(
            m::mock(GradebookService::class),
            m::mock(CharonGradingService::class)
                ->shouldReceive('updateGradeIfApplicable')->with($submission)
                ->getMock(),
            m::mock(RequestHandler::class),
            $submissionsRepository
        );

        $submissionService->addNewEmptySubmission($charon, 1);
    }

    public function testCalculatesSubmissionTotalGrade()
    {
        $gradeItem = m::mock('GradeItem');
        $gradeItem->calculation = '=##gi1## * ##gi2##';
        $charon = m::mock('Charon');
        $charon->course = 2;
        $charon->category = m::mock('Category', ['getGradeItem' => $gradeItem]);
        $submission = m::mock(Submission::class)->makePartial();
        $submission->charon = $charon;
        $submission->results = [
            $this->getResultWithIdNumber('Tests', 0.5),
            $this->getResultWithIdNumber('Style', 1),
        ];

        $submissionService = new SubmissionService(
            m::mock(GradebookService::class)
                ->shouldReceive('calculateResultFromFormula')
                ->with($gradeItem->calculation, [ 'tests' => 0.5, 'style' => 1 ], 2)
                ->andReturn(0.5)
                ->getMock(),
            m::mock(CharonGradingService::class),
            m::mock(RequestHandler::class),
            m::mock(SubmissionsRepository::class)
        );

        $result = $submissionService->calculateSubmissionTotalGrade($submission);

        $this->assertEquals(0.5, $result);
    }

    private function getResultWithIdNumber($idnumber, $calculatedResult)
    {
        $gradeItem = m::mock('GradeItem');
        $gradeItem->idnumber = $idnumber;
        $grademap = m::mock('Grademap');
        $grademap->gradeItem = $gradeItem;
        $result = m::mock('Result', ['getGrademap' => $grademap]);
        $result->calculated_result = $calculatedResult;
        return $result;
    }
}
