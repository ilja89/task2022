<?php

namespace Tests\Unit\Services;

use Mockery as m;
use Tests\TestCase;
use TTU\Charon\Services\HttpCommunicationService;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Services\TesterCommunicationService;

class TesterCommunicationServiceTest extends TestCase
{
    public function testSendsAddProjectInfo()
    {
        $grademap1 = m::mock(Grademap::class, ['getGradeTypeName' => 'Tests_1'])->makePartial();
        $grademap1->name = 'EX01 - Tests';
        $grademap1->grade_type_code = 1;
        $grademap2 = m::mock(Grademap::class, ['getGradeTypeName' => 'Style_1'])->makePartial();
        $grademap2->name = 'EX01 - Style';
        $grademap2->grade_type_code = 101;

        $charon = m::mock(Charon::class)->makePartial();
        $charon->id = 1;
        $charon->project_folder = 'EX01';
        $charon->testerType = new \StdClass;
        $charon->testerType->name = 'java';
        $charon->tester_extra = 'stylecheck';
        $charon->grademaps = [$grademap1, $grademap2];

        $httpCommunicator = m::mock(HttpCommunicationService::class)
            ->shouldReceive('postToTester')->with('addproject', [
                'id' => 1,
                'project'      => $charon->project_folder,
                'course'       => 'iti0020',
                'tester'       => $charon->testerType->name,
                'tester_extra' => $charon->tester_extra,
                'unittestsUrl' => 'ained.testid',
                'gradeMaps'    => [
                    [
                        'name'            => $grademap1->name,
                        'grade_type_name' => $grademap1->getGradeTypeName(),
                        'grade_type_code' => $grademap1->grade_type_code,
                    ],[
                        'name'            => $grademap2->name,
                        'grade_type_name' => $grademap2->getGradeTypeName(),
                        'grade_type_code' => $grademap2->grade_type_code,
                    ]
                ],
            ])
            ->getMock();
        $testerCommunicationService = new TesterCommunicationService($httpCommunicator);

        $testerCommunicationService->sendAddProjectInfo($charon, 'ained.testid', 'iti0020');
    }

    public function testSendsGitCallback()
    {
        $gitCallback = m::mock(GitCallback::class)->makePartial();
        $gitCallback->secret_token = 'Very secret token';

        $httpCommunicator = m::mock(HttpCommunicationService::class)
            ->shouldReceive('postToTester')->with('test', [
                'callback_url' => 'tester callback url',
                'secret_token' => $gitCallback->secret_token,
                'extra' => 'param'
            ])->getMock();
        $testerCommunicationService = new TesterCommunicationService($httpCommunicator);

        $testerCommunicationService->sendGitCallback($gitCallback, 'tester callback url', ['extra' => 'param']);
    }
}
