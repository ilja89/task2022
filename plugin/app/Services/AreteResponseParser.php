<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Models\SubmissionFile;
use TTU\Charon\Repositories\CharonRepository;

/**
 * This handles only the response requests from Arete
 */
class AreteResponseParser
{
    const MAX_32_BIT_DATETIME = 2147483647;

    /** @var CharonRepository */
    private $charonRepository;

    /** @var GitCallbackService */
    private $gitCallbackService;

    /**
     * RequestHandler constructor.
     *
     * @param CharonRepository $charonRepository
     * @param GitCallbackService $gitCallbackService
     */
    public function __construct(CharonRepository $charonRepository, GitCallbackService $gitCallbackService) {
        $this->charonRepository = $charonRepository;
        $this->gitCallbackService = $gitCallbackService;
    }

    /**
     * Gets the Submission from the given new Arete request.
     * The request should have the following keys: uniid, slug.
     * Mail, stdout, stderr are optional.
     *
     * @param Request $request
     * @param string $repository
     * @param int $authorId
     * @param string|null $courseId
     *
     * @return Submission
     */
    public function getSubmissionFromRequest(Request $request, string $repository,
                                             int $authorId, string $courseId = null): Submission
    {
        if (!empty($repository)) {
            $course = $this->gitCallbackService->getCourse($repository);
            $charon = $this->getCharon($request, $course->id);
        } else {
            $charon = $this->getCharon($request, intval($courseId));
        }

        $originalId = $request->has('retest') && !!$request->input('retest')
            ? $request->input('original_submission_id')
            : null;

        return new Submission([
            'charon_id' => $charon->id,
            'user_id' => $authorId,
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
    public function getResultFromRequest(int $submissionId, array $request, int $gradeCode)
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
            'contents' => isset($request['contents']) ? $request['contents'] : '',
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
