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
use TTU\Charon\Repositories\StudentsRepository;
use TTU\Charon\Services\CharonGradingService;
use TTU\Charon\Services\SubmissionService;
use TTU\Charon\Services\TestSuiteService;
use Zeizig\Moodle\Models\User;
use Zeizig\Moodle\Services\UserService;
use Zeizig\Moodle\Models\Course;

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
     * @param course $course
     * @param studentsRepository $studentsRepository
     */
    public function __construct(
        SubmissionService $submissionService,
        CharonGradingService $charonGradingService,
        UserService $userService,
        ResultRepository $resultRepository,
        TestSuiteService $testSuiteService,
        Course $course,
        StudentsRepository $studentsRepository
    ) {
        $this->submissionService = $submissionService;
        $this->charonGradingService = $charonGradingService;
        $this->userService = $userService;
        $this->resultRepository = $resultRepository;
        $this->testSuiteService = $testSuiteService;
        $this->course = $course;
        $this->studentsRepository = $studentsRepository;
    }
    /* Function needed to get course id using git callback
     * Takes ->
     * 	- GitCallback $call
     * Returns ->
     *  - int $courseId
     */
    
    public function getCourseIdFromGitCallBack(GitCallback $call)
    {
		$string = explode("/",$call->repo);
		$string = $string[1];
		$string = explode(".",$string);
		$string = $string[0];
		return $this->course->getCourseByName($string);
	}
	
	/* Function needed to get list of students related to exact course
	 * Takes ->
	 *  - int $courseId
	 * Returns ->
	 *  - array $nameList
	 */
	 
	public function getStudentsRelatedToCourse(int $courseId)
    {
		$nameList = null;
		$studentList=json_decode(json_encode($this->studentsRepository->searchStudentsByCourseAndKeyword($courseId,"")),true);
		for($i=0;$i<count($studentList);$i++)
		{
			$nameList[$i] = $studentList[$i]["username"];
		}
		return $nameList;
	}
	
	/* Function needed to pass only users who belong to course
	 * Returns only these array elements what exist in both arrays
	 * Takes ->
	 *  - Simple string array $usernames
	 *  - Simple string array $filter
	 * Returns ->
	 *  - Simple string array $filtered
	 * 
	 */
	public function usernamesFilter(array $usernames,array $filter)
	{
		sort($usernames);
		sort($filter);
		$filtered;
		for($i=0;$i<count($usernames);$i++)
		{
			for($c=0;$c<count($filter);$c++)
			{
				if($usernames[$i]==$filter[$c])
				{
					$filtered[] = $usernames[$i];
					break;
				}
			}
		}
		return $filtered;
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
		//print_r('Usernames-> '.json_encode($usernames)."\n\n");
        //print_r('Gitcallback-> '.json_encode($gitCallback)."\n\n");
        $students = $this->getStudentsRelatedToCourse($this->getCourseIdFromGitCallBack($gitCallback));
        //print_r('Related to course students-> '.json_encode($students)."\n\n");
        $usernames = $this->usernamesFilter($usernames,$students);
        //print_r('Passed users-> '.json_encode($usernames)."\n\n");
        $users = $this->getStudentsInvolved($usernames);
        //print_r('Users-> '.json_encode($users)."\n\n");

        $submission = $this->createNewSubmission($request, $gitCallback, $users[0]->id);

        $this->submissionService->saveFiles($submission->id, $request['files']);

        $submission->users()->saveMany($users);

        $this->saveResults($request, $submission, $users);

        $this->charonGradingService->calculateCalculatedResultsForNewSubmission($submission);

        $this->updateGrades($submission, $users);

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
