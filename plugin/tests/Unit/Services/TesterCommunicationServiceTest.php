<?php

namespace Tests\Unit\Services;

use Mockery as m;
use Tests\TestCase;
use TTU\Charon\Helpers\HttpCommunicator;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Services\TesterCommunicationService;

class TesterCommunicationServiceTest extends TestCase
{
    public function testSendsAddProjectInfo()
    {
        $grademap1 = m::mock('Grademap1');
        $grademap1->name = 'EX01 - Tests';
        $grademap1->gradeType = new \StdClass;
        $grademap1->gradeType->name = 'Tests_1';
        $grademap1->grade_type_code = 1;
        $grademap2 = m::mock('Grademap2');
        $grademap2->name = 'EX01 - Style';
        $grademap2->gradeType = new \StdClass;
        $grademap2->gradeType->name = 'Style_1';
        $grademap2->grade_type_code = 101;

        $charon = m::mock(Charon::class)->makePartial();
        $charon->id = 1;
        $charon->project_folder = 'EX01';
        $charon->testerType = new \StdClass;
        $charon->testerType->name = 'java';
        $charon->extra = 'stylecheck';
        $charon->grademaps = [$grademap1, $grademap2];

        $httpCommunicator = m::mock(HttpCommunicator::class)
            ->shouldReceive('sendInfoToTester')->with('addproject', 'post', [
                'id' => 1,
                'project'      => $charon->project_folder,
                'course'       => 'iti0020',
                'tester'       => $charon->testerType->name,
                'extra'        => $charon->extra,
                'unittestsUrl' => 'ained.testid',
                'gradeMaps'    => [
                    [
                        'name'            => $grademap1->name,
                        'grade_type_name' => $grademap1->gradeType->name,
                        'grade_type_code' => $grademap1->grade_type_code,
                    ],[
                        'name'            => $grademap2->name,
                        'grade_type_name' => $grademap2->gradeType->name,
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

        $httpCommunicator = m::mock(HttpCommunicator::class)
            ->shouldReceive('sendInfoToTester')->with('test', 'post', [
                'callback_url' => 'tester callback url',
                'secret_token' => $gitCallback->secret_token,
                'extra' => 'param'
            ])->getMock();
        $testerCommunicationService = new TesterCommunicationService($httpCommunicator);

        $testerCommunicationService->sendGitCallback($gitCallback, 'tester callback url', ['extra' => 'param']);
    }
}
