<?php

namespace TTU\Charon\Services\Flows;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use TTU\Charon\Http\Requests\TesterCallbackRequest;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\ResultRepository;
use TTU\Charon\Services\CharonGradingService;
use TTU\Charon\Services\SubmissionService;
use TTU\Charon\Services\TestSuiteService;
use Zeizig\Moodle\Models\User;
use Zeizig\Moodle\Services\UserService;

class SaveTesterCallback
{
    /** @var SubmissionService */
    private $submissionService;

    /** @var CharonGradingService */
    private $charonGradingService;

    /** @var UserService */
    private $userService;

    /** @var ResultRepository */
    private $resultRepository;

    /** @var TestSuiteService */
    private $testSuiteService;

    /**
     * @param SubmissionService $submissionService
     * @param CharonGradingService $charonGradingService
     * @param UserService $userService
     * @param ResultRepository $resultRepository
     * @param TestSuiteService $testSuiteService
     */
    public function __construct(
        SubmissionService $submissionService,
        CharonGradingService $charonGradingService,
        UserService $userService,
        ResultRepository $resultRepository,
        TestSuiteService $testSuiteService
    ) {
        $this->submissionService = $submissionService;
        $this->charonGradingService = $charonGradingService;
        $this->userService = $userService;
        $this->resultRepository = $resultRepository;
        $this->testSuiteService = $testSuiteService;
    }

    /**
     * Save a new submission from synchronous tester response.
     *
     * @param TesterCallbackRequest $request
     * @param User $user
     * @param int $courseId
     *
     * @return Submission
     * @throws Exception
     */
    public function saveTestersSyncResponse(TesterCallbackRequest $request, User $user, int $courseId): Submission
    {
        $users = [];

        if (!empty($request->input('returnExtra.usernames'))) {
            $usernames = collect($request->input('returnExtra.usernames'))
                ->map(function ($name) { return strtolower($name); })
                ->unique()
                ->values()
                ->all();
            $users = $this->getStudentsInvolved($usernames);
        }

        array_unshift($users, $user);

        return $this->executeSave($request, new GitCallback(), $users, $courseId);
    }

    /**
     * Save a new submission from asynchronous tester response.
     *
     * @param TesterCallbackRequest $request
     * @param GitCallback $gitCallback
     * @param array $usernames
     *
     * @return Submission
     * @throws Exception
     */
    public function saveTestersAsyncResponse(TesterCallbackRequest $request,GitCallback $gitCallback, array $usernames): Submission
    {
        $users = $this->getStudentsInvolved($usernames);

        return $this->executeSave($request, $gitCallback, $users);
    }

    /**
     * Save a new submission from tester data.
     *
     * @param TesterCallbackRequest $request
     * @param GitCallback $gitCallback
     * @param array $users
     * @param int|null $courseId
     *
     * @return Submission
     * @throws Exception
     */
    private function executeSave(TesterCallbackRequest $request, GitCallback $gitCallback, array $users, int $courseId = null): Submission
    {
        $submission = $this->createNewSubmission($request, $gitCallback, $users[0]->id, $courseId);

        if ($request['files']) {
            $this->submissionService->saveFiles($submission->id, $request['files']);
        } else if (
            $request->getContent() and
            array_key_exists('files', json_decode($request->getContent(), true))) {
            $this->submissionService
                ->saveFiles($submission->id, json_decode($request->getContent(), true)['files']);
        }

        $submission->users()->saveMany($users);

        $this->saveResults($request['testSuites'], (int) $request['style'], $submission, $users);

        $this->charonGradingService->calculateCalculatedResultsForNewSubmission($submission);

        $this->updateGrades($submission, $users);


        try {
            global $CFG;
            require_once ($CFG->dirroot . '/mod/charon/lib.php');
            foreach ($users as $student) {
                update_charon_completion_state($submission, $student->id);
            }
        } catch (\Exception $exception) {
            echo $exception;
            Log::error('Failed to update completion state. Likely culprit: course module. Error: ' . $exception->getMessage());
        }

        return $submission;
    }

    /**
     * @param array|string[] $usernames
     *
     * @return array|User[]
     * @throws InvalidArgumentException
     */
    private function getStudentsInvolved(array $usernames): array
    {
        $users = [];

        foreach ($usernames as $uniId) {
            $user = $this->userService->findUserByUniid($uniId);
            if ($user) {
                $users[$user->id] = $user;
            } else {
                Log::error("User was not found by Uni-ID:" . $uniId);
            }
        }

        if (empty($users)) {
            Log::error("Unable to find students for submission", $usernames);
            throw new InvalidArgumentException("Unable to find students for submission");
        }

        return array_values($users);
    }

    /**
     * Create a Submission, author is just to refer back to the student who pushed the code
     *
     * @param Request $request
     * @param GitCallback $gitCallback
     * @param int $authorId
     * @param int|null $courseId
     *
     * @return Submission
     * @throws Exception
     */
    private function createNewSubmission(Request $request, GitCallback $gitCallback, int $authorId, int $courseId = null): Submission
    {
        return $this->submissionService->saveSubmission($request, $gitCallback, $authorId, $courseId);
    }

    /**
     * Save test results for the submission.
     *
     * Includes style, test suite results and unused grades.
     *
     * @param array $testSuites
     * @param int $style
     *
     * @param Submission $submission
     * @param array|User[] $users
     */
    private function saveResults(array $testSuites, int $style, Submission $submission, array $users)
    {
        $results = $this->testSuiteService->saveSuites($testSuites, $submission->id);

        foreach ($users as $user) {
            foreach ($results as $result) {
                $result->replicate()
                    ->fill(['user_id' => $user->id])
                    ->save();
            }

            $this->resultRepository->saveIfGrademapPresent([
                'submission_id' => $submission->id,
                'user_id' => $user->id,
                'grade_type_code' => 101,
                'percentage' => $style == 100 ? 1 : 0,
                'calculated_result' => 0,
                'stdout' => null,
                'stderr' => null,
            ]);

            $this->submissionService->includeUnsentGrades($submission, $user->id);
        }
    }

    /**
     * For every student check if the submission improved the grade and update it.
     *
     * @param Submission $submission
     * @param array|User[] $users
     */
    private function updateGrades(Submission $submission, array $users)
    {
        foreach ($users as $user) {
            if ($this->charonGradingService->gradesShouldBeUpdated($submission, $user->id)) {
                $this->charonGradingService->updateGrade($submission, $user->id);
            }
        }
    }

    /**
     * Hide unnecessary fields so that the tester doesn't get duplicate information.
     *
     * @param Submission $submission
     */
    public function hideUnneededFields(Submission $submission)
    {
        $submission->makeHidden('charon');
        foreach ($submission->results as $result) {
            $result->makeHidden('submission');
        }
    }
}
