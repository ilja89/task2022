<?php

namespace TTU\Charon\Services;

use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\ResultRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
use Zeizig\Moodle\Services\GradingService;

/**
 * Class CharonGradingService.
 * This is names so in order to avoid naming conflicts with \Zeizig\Moodle\GradingService
 *
 * @package TTU\Charon\Services
 */
class CharonGradingService
{
    /** @var GradingService */
    private $gradingService;

    /** @var SubmissionsRepository */
    private $submissionsRepository;

    /** @var SubmissionCalculatorService */
    private $submissionCalculatorService;

    /** @var DefenseRegistrationRepository */
    private $defenseRegistrationRepository;

    /** @var ResultRepository */
    private $resultRepository;

    /**
     * CharonGradingService constructor.
     *
     * @param GradingService $gradingService
     * @param SubmissionsRepository $submissionsRepository
     * @param DefenseRegistrationRepository $defenseRegistrationRepository
     * @param SubmissionCalculatorService $submissionCalculatorService
     * @param ResultRepository $resultRepository
     */
    public function __construct(
        GradingService $gradingService,
        SubmissionsRepository $submissionsRepository,
        SubmissionCalculatorService $submissionCalculatorService,
        DefenseRegistrationRepository $defenseRegistrationRepository,
        ResultRepository $resultRepository
    ) {
        $this->gradingService = $gradingService;
        $this->submissionsRepository = $submissionsRepository;
        $this->submissionCalculatorService = $submissionCalculatorService;
        $this->defenseRegistrationRepository = $defenseRegistrationRepository;
        $this->resultRepository = $resultRepository;
    }

    /**
     * Update grades for the user.
     *
     * TODO: Send commit hash info to tester! Grade will now be updated!
     *
     * @param Submission $submission
     * @param int $userId
     *
     * @return void
     */
    public function updateGrades(Submission $submission, int $userId)
    {
        $charon = $submission->charon;
        $gradeTypeCodes = $charon->getGradeTypeCodes()->all();

        $results = $submission->results()
            ->where('user_id', $userId)
            ->whereIn('grade_type_code', $gradeTypeCodes)
            ->get();

        foreach ($results as $result) {
            $this->gradingService->updateGrade(
                $charon->course,
                $charon->id,
                $result->grade_type_code,
                $userId,
                $result->calculated_result
            );
        }
    }

    /**
     * Reset grades for the user.
     *
     * @param Charon $charon
     * @param int $userId
     *
     * @return void
     */
    public function resetGrades(Charon $charon, int $userId): void
    {
        foreach ($charon->getGradeTypeCodes()->all() as $gradeTypeCode) {
            $this->gradingService->updateGrade(
                $charon->course,
                $charon->id,
                $gradeTypeCode,
                $userId,
                0
            );
        }
    }

    /**
     * Check if the given submission should update grades.
     *
     * @param Submission $submission
     * @param int $studentId
     *
     * @return bool
     */
    public function gradesShouldBeUpdated(Submission $submission, int $studentId)
    {
        if ($this->hasConfirmedSubmission($submission->charon_id, $studentId)) {
            return false;
        }

        return $this->shouldUpdateBasedOnGradingMethod($submission, $studentId);
    }

    /**
     * Calculates the calculated results for given new submission and
     * saves them.
     *
     * @param Submission $submission
     *
     * @return void
     */
    public function calculateCalculatedResultsForNewSubmission(Submission $submission): void
    {
        $charon = $submission->charon;
        $results = $submission->results;

        for ($i = 0; $i < count($results); $i++) {

            $result = $results[$i];
            $bestEligibleResult = $charon->gradingMethod->isPreferBestEachGrade()
                ? $this->resultRepository->findResultsByCharonAndGradeType($charon->id, $result->grade_type_code)
                    ->filter(function ($r) use ($result) {
                        return $r->user_id === $result->user_id && $r->percentage <= $result->percentage;
                    })->reduce(function ($r1, $r2) {
                        return $r1 !== null && $r1->calculated_result > $r2->calculated_result
                            ? $r1
                            : $r2;
                    })
                : null;

            $result->calculated_result = $this->submissionCalculatorService->calculateResultFromDeadlines(
                $result,
                $charon->deadlines,
                $bestEligibleResult
            );

            $result->save();
        }
    }

    /**
     * Check if the submission has a previously confirmed submission.
     *
     * @param int $charonId
     * @param int $userId
     *
     * @return bool
     */
    public function hasConfirmedSubmission(int $charonId, int $userId)
    {
        return $this->submissionsRepository->charonHasConfirmedSubmissions(
            $charonId, $userId
        );
    }

    /**
     * Check if the submission should be updated based on the grading
     * method of the charon.
     *
     * @param Submission $submission
     * @param int $studentId
     *
     * @return bool
     */
    private function shouldUpdateBasedOnGradingMethod(Submission $submission, int $studentId)
    {
        $gradingMethod = $submission->charon->gradingMethod;
        if ($gradingMethod->isPreferBest() || $gradingMethod->isPreferBestEachGrade()) {
            return $this->submissionCalculatorService->submissionIsBetterThanActive($submission, $studentId);
        }

        return true;
    }

    /**
     * Recalculate grades for the given grademap.
     *
     * @param Grademap $grademap
     *
     * @return void
     */
    public function recalculateGrades(Grademap $grademap)
    {
        $results = $this->resultRepository->findResultsByCharonAndGradeType(
            $grademap->charon_id,
            $grademap->grade_type_code
        );

        $studentsResults = [];
        foreach ($results as $result) {
            if (array_key_exists($result->user_id, $studentsResults)) {
                array_push($studentsResults[$result->user_id], $result);
            } else {
                $studentsResults[$result->user_id] = [$result];
            }
        }

        foreach ($studentsResults as $studentId => $studentResults) {

            $bestResult = null;
            if (!$this->hasConfirmedSubmission($grademap->charon_id, $studentId)) {

                for ($i = 0; $i < count($studentResults); $i++) {
                    $studentResult = $studentResults[$i];

                    $studentResult->calculated_result =
                        $this->submissionCalculatorService->calculateResultFromDeadlines(
                            $studentResult,
                            $grademap->charon->deadlines,
                            $bestResult
                        );

                    $studentResult->save();
                    if ($bestResult === null || $studentResult->calculated_result > $bestResult->calculated_result) {
                        $bestResult = $studentResult;
                    }
                }
            }
        }
    }

    /**
     * Save defense progress by student id.
     *
     * Registration is updated by the student who registered this defense, everyone in the group still gets graded.
     *
     * @param $charonId
     * @param $submissionId
     * @param $studentId
     * @param $teacherId
     * @param $newProgress
     */
    public function updateProgressByStudentId($charonId, $submissionId, $studentId, $teacherId, $newProgress)
    {
        $studentRegistration = $this->defenseRegistrationRepository
            ->query()
            ->where('student_id', $studentId)
            ->where('submission_id', $submissionId)
            ->where('charon_id', $charonId)
            ->select('id')
            ->orderBy('choosen_time', 'desc')
            ->first();

        if (is_null($studentRegistration)) {
            return;
        }

        $this->defenseRegistrationRepository->updateRegistration($studentRegistration->id, $newProgress, $teacherId);
    }

    /**
     * Reset grades' calculated results if one does not have a confirmed submission and is a test grade.
     *
     * @param Grademap $grademap
     */
    public function resetGradesCalculatedResults(Grademap $grademap)
    {
        $resultIds = $this->resultRepository->findResultsByCharonAndGradeType(
            $grademap->charon_id,
            $grademap->grade_type_code
        )->filter(function ($result) use ($grademap) {
            return !$this->hasConfirmedSubmission($grademap->charon_id, $result->user_id);
        })->pluck("id")->all();

        $this->resultRepository->resetResultsCalculatedResults($resultIds);
    }
}
