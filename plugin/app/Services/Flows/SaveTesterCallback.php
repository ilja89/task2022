<?php

namespace TTU\Charon\Services\Flows;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use TTU\Charon\Http\Requests\TesterCallbackRequest;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\ResultRepository;
use TTU\Charon\Services\AreteResponseParser;
use TTU\Charon\Services\CharonGradingService;
use TTU\Charon\Services\GitCallbackService;
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

    /** @var AreteResponseParser */
    private $areteResponseParser;

    /** @var GitCallbackService */
    private $gitCallbackService;

    /**
     * @param SubmissionService $submissionService
     * @param CharonGradingService $charonGradingService
     * @param UserService $userService
     * @param ResultRepository $resultRepository
     * @param TestSuiteService $testSuiteService
     * @param AreteResponseParser $areteResponseParser
     * @param GitCallbackService $gitCallbackService
     */
    public function __construct(
        SubmissionService $submissionService,
        CharonGradingService $charonGradingService,
        UserService $userService,
        ResultRepository $resultRepository,
        TestSuiteService $testSuiteService,
        AreteResponseParser $areteResponseParser,
        GitCallbackService $gitCallbackService
    ) {
        $this->submissionService = $submissionService;
        $this->charonGradingService = $charonGradingService;
        $this->userService = $userService;
        $this->resultRepository = $resultRepository;
        $this->testSuiteService = $testSuiteService;
        $this->areteResponseParser = $areteResponseParser;
        $this->gitCallbackService = $gitCallbackService;
    }

    /**
     * Save a new submission from synchronous tester response.
     *
     * @param TesterCallbackRequest $request
     * @param User $user
     * @param Charon $charon
     * @param array $usernames
     *
     * @return Submission
     * @throws Exception
     */
    public function saveTestersSyncResponse(TesterCallbackRequest $request, User $user, Charon $charon, array $usernames): Submission
    {
        $users = [];
        if ($usernames) {
            $users = $this->getStudentsInvolved($usernames);
        }

        array_unshift($users, $user);

        return $this->executeSave($request, $charon, $users);
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
    public function saveTestersAsyncResponse(TesterCallbackRequest $request, GitCallback $gitCallback, array $usernames): Submission
    {
        $users = $this->getStudentsInvolved($usernames);

        $request['gitCallBackId'] = $gitCallback->id;

        $course = $this->gitCallbackService->getCourse($gitCallback->repo);
        $charon = $this->areteResponseParser->getCharon($request, $course->id);

        return $this->executeSave($request, $charon, $users);
    }

    /**
     * Save a new submission from tester data.
     *
     * @param TesterCallbackRequest $request
     * @param Charon $charon
     * @param array $users
     * @return Submission
     */
    private function executeSave(TesterCallbackRequest $request, Charon $charon, array $users): Submission
    {
        global $CFG;
        require_once ($CFG->dirroot . '/mod/charon/lib.php');

        $submission = $this->createNewSubmission($request, $charon, $users[0]->id);

        $this->submissionService->saveFiles($submission->id, $request['files']);

        $submission->users()->saveMany($users);

        $this->saveResults($request['testSuites'], (int) $request['style'], $submission, $users);

        $this->charonGradingService->calculateCalculatedResultsForNewSubmission($submission);

        $this->updateGrades($submission, $users);

        foreach ($users as $student) {
            try {
                update_charon_completion_state($submission, $student->id);
            } catch (\Exception $exception) {
                Log::error('Failed to update completion state. Likely culprit: course module. Error: ' . $exception->getMessage());
            }
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
     * @param Charon $charon
     * @param int $authorId
     * @return Submission
     */
    private function createNewSubmission(Request $request, Charon $charon, int $authorId): Submission
    {
        return $this->submissionService->saveSubmission($request, $charon, $authorId);
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
