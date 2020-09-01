<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use mysql_xdevapi\Exception;
use TTU\Charon\Exceptions\ResultPointsRequiredException;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\GitCallback;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Models\TestSuite;
use TTU\Charon\Models\UnitTest;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
use Zeizig\Moodle\Services\GradebookService;

/**
 * Class SubmissionService.
 *
 * @package TTU\Charon\Services
 */
class SubmissionService
{
    /** @var GradebookService */
    private $gradebookService;

    /** @var CharonGradingService */
    private $charonGradingService;

    /** @var RequestHandlingService */
    private $requestHandlingService;

    /** @var SubmissionsRepository */
    private $submissionsRepository;

    /** @var DefenseRegistrationRepository */
    private $defenseRegistrationRepository;

    /**
     * SubmissionService constructor.
     *
     * @param GradebookService $gradebookService
     * @param CharonGradingService $charonGradingService
     * @param RequestHandlingService $requestHandlingService
     * @param SubmissionsRepository $submissionsRepository
     * @param DefenseRegistrationRepository $defenseRegistrationRepository
     */
    public function __construct(
        GradebookService $gradebookService,
        CharonGradingService $charonGradingService,
        RequestHandlingService $requestHandlingService,
        SubmissionsRepository $submissionsRepository,
        DefenseRegistrationRepository $defenseRegistrationRepository
    )
    {
        $this->gradebookService = $gradebookService;
        $this->charonGradingService = $charonGradingService;
        $this->requestHandlingService = $requestHandlingService;
        $this->submissionsRepository = $submissionsRepository;
        $this->defenseRegistrationRepository = $defenseRegistrationRepository;
    }

    /**
     * Saves the Submission from the given request.
     * Also saves the Results and Submission Files.
     *
     * @param Request $submissionRequest
     * @param GitCallback $gitCallback
     *
     * @return Submission
     * @throws \Exception
     */
    public function saveSubmission($submissionRequest, $gitCallback)
    {
        $submission = $this->requestHandlingService->getSubmissionFromRequest($submissionRequest, $gitCallback);
        $submission->git_callback_id = $gitCallback->id;
        $submission->save();
        $this->saveSuitesAndTests($submissionRequest, $submission);

        $style = (int)$submissionRequest['style'] == 100;

        $result = new Result([
            'submission_id' => $submission->id,
            'grade_type_code' => 101,
            'percentage' => $style ? 1 : 0,
            'calculated_result' => 0,
            'stdout' => null,
            'stderr' => null,
        ]);

        if ($result->getGrademap() != null) {
            $result->save();
        }

        $this->saveResults($submission, $submissionRequest['testSuites']);

        $this->saveFiles($submission, $submissionRequest['files']);


        return $submission;
    }

    /**
     * @param $submissionRequest
     * @param $submission
     */
    private function saveSuitesAndTests($submissionRequest, $submission)
    {
        foreach ($submissionRequest['testSuites'] as $testSuite) {
            $createdTestSuite = TestSuite::create([
                'submission_id' => $submission->id,
                'name' => $testSuite['name'],
                'file' => $testSuite['file'],
                'start_date' => $this->constructDate($testSuite['startDate']),
                'end_date' => $this->constructDate($testSuite['endDate']),
                'weight' => $testSuite['weight'] == null ? 1 : $testSuite['weight'],
                'passed_count' => $testSuite['passedCount'],
                'grade' => $testSuite['grade']
            ]);
            $createdTestSuite->save();
            foreach ($testSuite['unitTests'] as $unitTest) {
                $createdUnitTest = UnitTest::create([
                    'test_suite_id' => $createdTestSuite->id,
                    'groups_depended_upon' => $this->handleMaybeLists($unitTest['groupsDependedUpon']),
                    'status' => $unitTest['status'],
                    'weight' => $unitTest['weight'] == null ? 1 : $unitTest['weight'],
                    'print_exception_message' => $unitTest['printExceptionMessage'],
                    'print_stack_trace' => $unitTest['printStackTrace'],
                    'time_elapsed' => $unitTest['timeElapsed'],
                    'methods_depended_upon' => $this->handleMaybeLists($unitTest['methodsDependedUpon']),
                    'stack_trace' => $this->constructStackTrace($unitTest['stackTrace']),
                    'name' => $unitTest['name'],
                    'stdout' => $this->handleMaybeLists($unitTest['stdout']),
                    'exception_class' => $unitTest['exceptionClass'],
                    'exception_message' => $unitTest['exceptionMessage'],
                    'stderr' => $this->handleMaybeLists($unitTest['stderr'])
                ]);
                $createdUnitTest->save();
            }
        }
    }


    /**
     * Save the results from given results request (arete v2).
     *
     * @param Submission $submission
     * @param array $resultsRequest
     *
     * @return void
     */
    private function saveResults($submission, $resultsRequest)
    {
        $gradeCode = 1;
        foreach ($resultsRequest as $resultRequest) {  // testSuite in testSuites
            $result = $this->requestHandlingService->getResultFromRequest($submission->id, $resultRequest, $gradeCode++);
            $result->save();
        }

        $this->includeUnsentGrades($submission);
    }

    /**
     * Save the files from given results request (arete v2).
     *
     * @param Submission $submission
     * @param array $filesRequest
     *
     * @return void
     */
    private function saveFiles($submission, $filesRequest)
    {
        foreach ($filesRequest as $fileRequest) {
            $submissionFile = $this->requestHandlingService->getFileFromRequest($submission->id, $fileRequest, false);
            $submissionFile->save();
        }
    }

    /**
     * Updates the given submissions' results with the given new results.
     *
     * @param Charon $charon
     * @param Submission $submission
     * @param array $newResults
     *
     * @return Submission
     * @throws ResultPointsRequiredException
     */
    public function updateSubmissionCalculatedResults(Charon $charon, Submission $submission, $newResults)
    {
        foreach ($newResults as $result) {
            if ($result['calculated_result'] !== '0' && !$result['calculated_result']) {
                throw (new ResultPointsRequiredException('result_points_are_required'))
                    ->setResultId($result['id']);
            }
        }
        $this->defenseRegistrationRepository->saveProgressByStudentId($charon->id, $submission->user_id, 'Done');

        foreach ($newResults as $result) {

            $existingResult = $submission->results->first(function ($resultLoop) use ($result) {
                return $resultLoop->id == $result['id'];
            });

            $existingResult->calculated_result = $result['calculated_result'];
            $existingResult->save();
        }

        $this->charonGradingService->updateGradeIfApplicable($submission, true);
        $this->charonGradingService->confirmSubmission($submission);

        return $submission;
    }

    /**
     * Adds a new empty submission for the given user.
     *
     * @param Charon $charon
     * @param int $studentId
     *
     * @return Submission
     */
    public function addNewEmptySubmission(Charon $charon, $studentId)
    {
        $now = Carbon::now();
        $now = $now->setTimezone('UTC');
        /** @var Submission $submission */
        $submission = $charon->submissions()->create([
            'user_id' => $studentId,
            'git_hash' => '',
            'git_timestamp' => $now,
            'created_at' => $now,
            'updated_at' => $now,
            'stdout' => 'Manually created by teacher',
        ]);

        foreach ($charon->grademaps as $grademap) {
            $this->submissionsRepository->saveNewEmptyResult($submission->id, $grademap->grade_type_code, '');
        }

        $this->charonGradingService->updateGradeIfApplicable($submission);

        return $submission;
    }

    /**
     * Calculates the total grade for the given submission.
     *
     * @param Submission $submission
     *
     * @return float
     */
    public function calculateSubmissionTotalGrade(Submission $submission)
    {
        $charon = $submission->charon;
        $calculation = $charon->category->getGradeItem()->calculation;

        if ($calculation !== null) {
            $params = [];
            foreach ($submission->results as $result) {
                $params[strtolower($result->getGrademap()->gradeItem->idnumber)] = $result->calculated_result;
            }

            return $this->gradebookService->calculateResultFromFormula(
                $calculation, $params, $charon->course
            );
        } else {
            $sum = 0;
            foreach ($submission->results as $result) {
                $sum += $result->calculated_result;
            }

            return $sum;
        }
    }

    /**
     * Include custom grades for the given submission. If custom grademaps exist
     * will create new result for them.
     *
     * @param Submission $submission
     *
     * @return void
     */
    private function includeUnsentGrades(Submission $submission)
    {
        $charon = $submission->charon;

        if (!$charon) {
            return;
        }

        foreach ($charon->grademaps as $grademap) {

            $result = $submission->results->first(function ($result) use ($grademap) {
                /** @var Result $result */
                return $result->grade_type_code === $grademap->grade_type_code;
            });

            if ($result !== null) {
                continue;
            }

            $this->submissionsRepository->saveNewEmptyResult(
                $submission->id,
                $grademap->grade_type_code,
                'This result was automatically generated'
            );
        }
    }

    /**
     * @param $stackTrace
     * @return string
     */
    private function constructStackTrace($stackTrace)
    {
        if ($stackTrace != null) {
            return strlen($stackTrace) >= 255 ? substr($stackTrace, 0, 255) : $stackTrace;
        }
        return '';
    }

    /**
     * @param $list
     * @return string
     */
    private function handleMaybeLists($list)
    {
        try {
            return implode(', ', $list);
        } catch (\Exception $e) {
            return "";
        }

    }

    /**
     * @param $date
     * @return string|null
     */
    private function constructDate($date)
    {
        if ($date == null) {
            return $date;
        }

        if ($date < 2147483647) {
            return Carbon::createFromTimestamp($date)->format('Y-m-d H:i:s');
        }

        return Carbon::createFromTimestamp((int)($date / 1000))->format('Y-m-d H:i:s');
    }
}
