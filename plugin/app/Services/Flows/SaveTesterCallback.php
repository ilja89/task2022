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
use Zeizig\Moodle\Models\Course as modelsCourse;
use TTU\Charon\Services\GitCallbackService;

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
     * @param gitCallbackService $gitCallbackService
     * @param modelsCourse $modelsCourse
     */
    public function __construct(
        SubmissionService $submissionService,
        CharonGradingService $charonGradingService,
        UserService $userService,
        ResultRepository $resultRepository,
        TestSuiteService $testSuiteService,
        GitCallbackService $gitCallbackService,
        modelsCourse $modelsCourse
    ) {
        $this->submissionService = $submissionService;
        $this->charonGradingService = $charonGradingService;
        $this->userService = $userService;
        $this->resultRepository = $resultRepository;
        $this->testSuiteService = $testSuiteService;
        $this->gitCallbackService = $gitCallbackService;
        $this->modelsCourse = $modelsCourse;
    }

    /**
     * Save a new submission from tester data.
     *
     * @param TesterCallbackRequest $request
     * @param GitCallback $gitCallback
     * @param array $usernames
     *
     * @throws Exception
     * @return Submission
     */
    public function run(TesterCallbackRequest $request, GitCallback $gitCallback, array $usernames)
    {
        $courseId = $this->gitCallbackService->getCourse($gitCallback->repo)->id;

        $students = $this->modelsCourse->getNamesOfStudentsRelatedToCourse($courseId);

        $users = $this->getStudentsInvolved($usernames, $students);

        $submission = $this->createNewSubmission($request, $gitCallback, $users[0]->id);

        $this->submissionService->saveFiles($submission->id, $request['files']);

        $submission->users()->saveMany($users);

        $this->saveResults($request, $submission, $users);

        $this->charonGradingService->calculateCalculatedResultsForNewSubmission($submission);

        $this->updateGrades($submission, $users);

        return $submission;
    }

    /** Get list of students who have to pass to submission
     * @param array $usernames
     * @param array $studentNamesArray
     *
     * @return array
     */
    private function getStudentsInvolved(array $usernames, array $studentNamesArray): array
    {
        for ($i=0; $i < count($studentNamesArray); $i++)
        {
            $filter[$i]=$studentNamesArray[$i]->username;
        }
        sort($usernames);
        sort($filter);
        $users = [];

        foreach ($usernames as $uniId) {
            $user = $this->userService->findUserByUniid($uniId);
            if (!$user) {
                Log::error("User was not found by Uni-ID:" . $uniId);
            }
            else if(!in_array($uniId,$filter)){
                Log::error("User doesn't belong to course:" . $uniId);
            } else {
                $users[$user->id] = $user;
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
     *
     * @return Submission
     * @throws Exception
     */
    private function createNewSubmission(Request $request, GitCallback $gitCallback, int $authorId)
    {
        return $this->submissionService->saveSubmission($request, $gitCallback, $authorId);
    }

    /**
     * Save test results for the submission.
     *
     * Includes style, test suite results and unused grades.
     *
     * @param TesterCallbackRequest $request
     * @param Submission $submission
     * @param array|User[] $users
     */
    private function saveResults(TesterCallbackRequest $request, Submission $submission, array $users)
    {
        $results = $this->testSuiteService->saveSuites($request['testSuites'], $submission->id);

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
                'percentage' => (int) $request['style'] == 100 ? 1 : 0,
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
}
