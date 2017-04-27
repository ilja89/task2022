<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Models\SubmissionFile;
use Zeizig\Moodle\Services\UserService;

class RequestHandlingService
{
    /** @var UserService */
    private $userService;

    /**
     * RequestHandler constructor.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Get a file from the given request array.
     *
     * @param  int  $submissionId
     * @param  array  $request
     *
     * @return SubmissionFile
     */
    public function getFileFromRequest($submissionId, $request)
    {
        return new SubmissionFile([
            'submission_id' => $submissionId,
            'path'     => $request['path'],
            'contents' => $request['contents'],
        ]);
    }

    /**
     * Gets the Submission from the given request.
     * The request should have the following keys: git_timestamp, charon_id, uni_id, git_hash.
     * Mail, stdout, stderr are optional.
     *
     * @param  Request $request
     *
     * @return Submission
     */
    public function getSubmissionFromRequest($request)
    {
        $now = Carbon::now(config('app.timezone'));
        $now = $now->setTimezone('UTC');
        $gitTimestamp = isset($request['git_timestamp'])
            ? Carbon::createFromTimestamp($request['git_timestamp'], config('app.timezone'))
            : $now;
        $gitTimestamp->setTimezone('UTC');

        $submission = new Submission([
            'charon_id'          => $request['charon_id'],
            'user_id'            => $this->userService->findUserByIdNumber($request['uni_id'])->id,
            'git_hash'           => $request['git_hash'],
            'git_timestamp'      => $gitTimestamp,
            'mail'               => isset($request['mail']) ? $request['mail'] : null,
            'stdout'             => isset($request['stdout']) ? $request['stdout'] : null,
            'stderr'             => isset($request['stderr']) ? $request['stderr'] : null,
            'git_commit_message' => isset($request['git_commit_message']) ? $request['git_commit_message'] : null,
            'created_at'         => $now,
            'updated_at'         => $now,
        ]);

        return $submission;
    }

    /**
     * This gets the result from given request. The calculated result is set to 0 by
     * default and will be calculated later.
     *
     * Example request:
     * {
     *     "grade_type_code": 1,
     *     "percentage": 100,
     *     "stdout": "Some result specific stdout",  // Optional
     *     "stderr": "Some result specific stderr"  // Optional
     * }
     *
     * @param  integer $submissionId
     * @param  array $request
     *
     * @return Result
     */
    public function getResultFromRequest($submissionId, $request)
    {
        return new Result([
            'submission_id'     => $submissionId,
            'grade_type_code'   => $request['grade_type_code'],
            'percentage'        => floatval($request['percentage']) / 100,
            'calculated_result' => 0,
            'stdout'            => isset($request['stdout']) ? $request['stdout'] : null,
            'stderr'            => isset($request['stderr']) ? $request['stderr'] : null,
        ]);
    }
}
