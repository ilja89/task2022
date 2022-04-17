<?php

namespace Tests\Unit\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;
use Mockery\Mock;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use TTU\Charon\Exceptions\ResultPointsRequiredException;
use TTU\Charon\Http\Controllers\Api\FilesController;
use TTU\Charon\Http\Controllers\Api\SubmissionsController;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Services\Flows\TeacherModifiesSubmission;
use TTU\Charon\Services\SubmissionService;

class SubmissionControllerTest extends TestCase
{
    /** @var Mock|Request */
    private $request;

    /** @var Mock|TeacherModifiesSubmission */
    private $teacherModifiesSubmission;

    /** @var SubmissionsController */
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new SubmissionsController(
            $this->request = Mockery::mock(Request::class),
            Mockery::mock(SubmissionService::class),
            Mockery::mock(SubmissionsRepository::class),
            Mockery::mock(CharonRepository::class),
            Mockery::mock(FilesController::class),
            $this->teacherModifiesSubmission = Mockery::mock(TeacherModifiesSubmission::class)
        );
    }

    public function testSaveSubmissionThrowsIfMissingResults()
    {
        $this->expectException(ResultPointsRequiredException::class);

        $this->request
            ->shouldReceive('input')
            ->once()
            ->with('submission.results')
            ->andReturn([['calculated_result' => '', 'id' => 3]]);

        $this->controller->saveSubmission(new Charon(), new Submission());
    }

    /**
     * @throws ResultPointsRequiredException
     */
    public function testSaveSubmissionReturns200OnSuccess()
    {
        $this->request
            ->shouldReceive('input')
            ->once()
            ->with('submission.results')
            ->andReturn([['calculated_result' => 50]]);

        $submission = new Submission();
        $charon = new Charon();

        $this->teacherModifiesSubmission
            ->shouldReceive('run')
            ->with($submission, $charon, [['calculated_result' => 50]]);

        /** @var JsonResponse|Response $response */
        $response = $this->controller->saveSubmission($charon, $submission);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Submission saved!', $response->getData()->data->message);
    }
}
