<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Models\SubmissionFile;
use Zeizig\Moodle\Models\Course;
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
     * Gets the Submission from the given new Arete request.
     * The request should have the following keys: uniid, slug.
     * Mail, stdout, stderr are optional.
     *
     * @param  Request $request
     * @param  GitCallback $gitCallback
     *
     * @return Submission
     */
    public function getSubmissionFromRequest($request, $gitCallback)
    {
        $now = Carbon::now(config('app.timezone'));
        $now = $now->setTimezone('UTC');
        $gitTimestamp = $request->has('git_timestamp')
            ? Carbon::createFromTimestamp($request->input('git_timestamp'), config('app.timezone'))
            : $now;
        $gitTimestamp->setTimezone('UTC');

        $uniId = $request->input('uniid');
        $student = $this->userService->findUserByIdNumber($uniId);
        $studentId = $student->id;

        $courseIdCode = "";
        $repo = $gitCallback->repo;
        if (strpos($repo, "exams")) {
            if (preg_match('/gitlab.cs.ttu.ee:([a-zA-Z0-9_.-]+)\/exams/', $repo, $matches)) {
                $courseIdCode = $matches[1];
            }
        } else {
            if (preg_match('/gitlab.cs.ttu.ee:([a-zA-Z0-9_.-]+)\/([a-zA-Z0-9_.-]+)\.git/', $repo, $matches)) {
                $courseIdCode = $matches[2];
            }
        }
        Log::info("Course id code:" . $courseIdCode);
        $course = Course::where('shortname', $courseIdCode)->first();
        $charon = Charon::where([
        ['project_folder', $request->input("slug")],
        ['course', $course->id]])->first();

        $output = "";
        $stackOutput = "";
        if ($request->has("testSuites")) {
            foreach ($request['testSuites'] as $suite) {
                foreach ($suite['unitTests'] as $test) {
                    if ($test['stackTrace']) {
                        $stackOutput .= "\n\n" . $test['stackTrace'];
                    }
                }
            }
        }
        if ($stackOutput) {
            $output = $stackOutput;
        }
        // add original output
        $output .= "\n" . $request['consoleOutputs'][0]['content'];

        $submission = new Submission([
            'charon_id'          => $charon->id,
            'user_id'            => $studentId,
            'git_hash'           => $request->input('hash'),
            'git_timestamp'      => $gitTimestamp,
            'mail'               => $request->input('output'),
            'stdout'             => $output,
            'stderr'             => 'stderr',
            'git_commit_message' => $request->input('message'),
            'created_at'         => $now,
            'updated_at'         => $now,
            'original_submission_id' => $request->has('retest') && !! $request->input('retest')
                ? $request->input('original_submission_id')
                : null,
        ]);

        return $submission;
    }

    /**
     * This gets the result from given request (arete v2). The calculated result is set to 0 by
     * default and will be calculated later.
     *
     * Example request:
     * {
     *     "grade": 100
     * }
     *
     * TODO: parse results for each test
     *
     * @param integer $submissionId
     * @param array $request
     * @param int $gradeCode
     *
     * @return Result
     */
    public function getResultFromRequest($submissionId, $request, $gradeCode)
    {
        return new Result([
            'submission_id'     => $submissionId,
            'grade_type_code'   => $gradeCode,
            'percentage'        => floatval($request['grade']) / 100,
            'calculated_result' => 0,
            'stdout'            => null,
            'stderr'            => null,
        ]);
    }

    /**
     * Get a file from the given request array (arete v2).
     *
     * @param int $submissionId
     * @param array $request
     * @param bool $isTest
     *
     * @return SubmissionFile
     */
    public function getFileFromRequest($submissionId, $request, $isTest)
    {
        return new SubmissionFile([
            'submission_id' => $submissionId,
            'path'     => $request['path'],
            'contents' => $request['contents'],
            'is_test'  => $isTest,
        ]);
    }

}
