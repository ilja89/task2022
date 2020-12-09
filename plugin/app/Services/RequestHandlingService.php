<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Models\SubmissionFile;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\CourseRepository;
use Zeizig\Moodle\Models\User;
use Zeizig\Moodle\Services\UserService;

/**
 * This handles only the response requests from Arete
 */
class RequestHandlingService
{
    const MAX_32_BIT_DATETIME = 2147483647;

    /** @var UserService */
    private $userService;

    /** @var CharonRepository */
    private $charonRepository;

    /** @var CourseRepository */
    private $courseRepository;

    /** @var GitCallbackService */
    private $gitCallbackService;

    /**
     * RequestHandler constructor.
     *
     * @param UserService $userService
     * @param CharonRepository $charonRepository
     * @param CourseRepository $courseRepository
     * @param GitCallbackService $gitCallbackService
     */
    public function __construct(
        UserService $userService,
        CharonRepository $charonRepository,
        CourseRepository $courseRepository,
        GitCallbackService $gitCallbackService
    ) {
        $this->userService = $userService;
        $this->charonRepository = $charonRepository;
        $this->courseRepository = $courseRepository;
        $this->gitCallbackService = $gitCallbackService;
    }

    /**
     * Gets the Submission from the given new Arete request.
     * The request should have the following keys: uniid, slug.
     * Mail, stdout, stderr are optional.
     *
     * @param Request $request
     * @param GitCallback $gitCallback
     *
     * @return Submission
     * @throws Exception
     */
    public function getSubmissionFromRequest(Request $request, GitCallback $gitCallback)
    {
        $course = $this->gitCallbackService->getCourse($gitCallback->repo);
        $charon = $this->getCharon($request, $course->id);
        $studentId = $this->getUserId($request->input('uniid'));

        $originalId = $request->has('retest') && !!$request->input('retest')
            ? $request->input('original_submission_id')
            : null;

        return new Submission([
            'charon_id' => $charon->id,
            'user_id' => $studentId,
            'git_hash' => $request->input('hash'),
            'git_timestamp' => $this->getGitTimestamp($request),
            'mail' => $request->input('output'),
            'stdout' => $this->getStdOut($request),
            'stderr' => 'stderr',
            'git_commit_message' => $request->input('message'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'original_submission_id' => $originalId,
        ]);
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
            'submission_id' => $submissionId,
            'grade_type_code' => $gradeCode,
            'percentage' => floatval($request['grade']) / 100,
            'calculated_result' => 0,
            'stdout' => null,
            'stderr' => null,
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
            'path' => $request['path'],
            'contents' => $request['contents'],
            'is_test' => $isTest,
        ]);
    }

    /**
     * @param Request $request
     * @return DateTime
     */
    private function getGitTimestamp(Request $request)
    {
        if (!$request->has('timestamp')) {
            return Carbon::now();
        }
        return $request->input('timestamp') < self::MAX_32_BIT_DATETIME
            ? Carbon::createFromTimestamp($request->input('timestamp'))
            : Carbon::createFromTimestamp((int)($request->input('timestamp') / 1000));
    }

    /**
     * @param string $uniId
     * @return int
     * @throws ModelNotFoundException
     */
    private function getUserId(string $uniId)
    {
        $user = $this->userService->findUserByUniid($uniId);
        if ($user) {
            return $user->id;
        }
        Log::error("User was not found by Uni-ID:" . $uniId);
        throw (new ModelNotFoundException)->setModel(User::class);
    }

    /**
     * @param Request $request
     * @param int $courseId
     * @return Charon|Model
     * @throws ModelNotFoundException
     */
    private function getCharon(Request $request, int $courseId)
    {
        if ($request->input('returnExtra.charon')) {
            $query = [['id', $request->input('returnExtra.charon')]];
        } else {
            $query = [['project_folder', $request->input('slug')], ['course', $courseId]];
        }

        try {
            return $this->charonRepository
                ->query()
                ->where($query)
                ->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            Log::error('Charon was not found by fields:', $query);
            throw $exception;
        }
    }

    /**
     * Stitch together test stack traces and original console output
     *
     * @param Request $request
     * @return string
     */
    private function getStdOut(Request $request)
    {
        if (!$request->has('testSuites')) {
            return $request['consoleOutputs'];
        }

        $output = collect($request->input('testSuites'))
            ->filter(function ($suite) {
                return isset($suite['unitTests']);
            })
            ->flatMap(function ($suite) {
                return $suite['unitTests'];
            })
            ->filter(function ($test) {
                return isset($test['stackTrace']);
            })
            ->map(function ($test) {
                return $test['stackTrace'];
            })
            ->implode('\n\n');

        return $output . '\n' . $request['consoleOutputs'];
    }
}
