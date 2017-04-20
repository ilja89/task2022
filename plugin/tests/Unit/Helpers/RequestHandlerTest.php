<?php

namespace Tests\Unit\Helpers;

use Mockery as m;
use Tests\TestCase;
use TTU\Charon\Helpers\RequestHandlingService;
use Zeizig\Moodle\Services\UserService;

class RequestHandlerTest extends TestCase
{
    public function testGetFileFromRequest()
    {
        $request = ['path' => 'EX01/directory/structure/file.java', 'contents' => 'hello world'];

        $requestHandler = new RequestHandlingService(m::mock(UserService::class));

        $file = $requestHandler->getFileFromRequest(1, $request);

        $this->assertEquals(1, $file->submission_id);
        $this->assertEquals($request['path'], $file->path);
        $this->assertEquals($request['contents'], $file->contents);
    }

    public function testGetSubmissionFromRequest()
    {
        $request = [
            'charon_id' => 1,
            'uni_id' => 'uni.id',
            'git_hash' => 'asdfgh',
            'git_timestamp' => '1488932760',
            'mail' => 'mail here',
            'stdout' => 'stdout here',
            'stderr' => 'stderr here',
            'git_commit_message' => 'git commit msg',
        ];
        $user = m::mock('user');
        $user->id = 2;

        $requestHandler = new RequestHandlingService(
            m::mock(UserService::class)->shouldReceive('findUserByIdNumber')->andReturn($user)->getMock()
        );

        $submission = $requestHandler->getSubmissionFromRequest($request);

        $this->assertEquals(1, $submission->charon_id);
        $this->assertEquals($request['mail'], $submission->mail);
        $this->assertEquals($request['stdout'], $submission->stdout);
        $this->assertEquals($user->id, $submission->user_id);
    }

    public function testGetResultFromRequest()
    {
        $request = [
            'grade_type_code' => 1,
            'percentage' => 50,
            'stdout' => 'stdout text',
            'stderr' => 'stderr text',
        ];

        $requestHandler = new RequestHandlingService(m::mock(UserService::class));
        $result = $requestHandler->getResultFromRequest(1, $request);

        $this->assertEquals(1, $result->submission_id);
        $this->assertEquals(0, $result->calculated_result);
        $this->assertEquals(0.5, $result->percentage);
    }
}
