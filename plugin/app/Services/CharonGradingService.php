<?php

namespace TTU\Charon\Services;

use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
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

    /**
     * CharonGradingService constructor.
     *
     * @param GradingService $gradingService
     * @param SubmissionsRepository $submissionsRepository
     * @param DefenseRegistrationRepository $defenseRegistrationRepository
     * @param SubmissionCalculatorService $submissionCalculatorService
     */
    public function __construct(
        GradingService $gradingService,
        SubmissionsRepository $submissionsRepository,
        SubmissionCalculatorService $submissionCalculatorService,
        DefenseRegistrationRepository $defenseRegistrationRepository
    ) {
        $this->gradingService = $gradingService;
        $this->submissionsRepository = $submissionsRepository;
        $this->submissionCalculatorService = $submissionCalculatorService;
        $this->defenseRegistrationRepository = $defenseRegistrationRepository;
    }

    /**
     * Update the grade for the user.
     *
     * TODO: Send commit hash info to tester! Grade will now be updated!
     *
     * @param Submission $submission
     * @param int $userId
     *
     * @return void
     */
    public function updateGrade(Submission $submission, int $userId)
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
    public function calculateCalculatedResultsForNewSubmission(Submission $submission)
    {
        $charon = $submission->charon;
        foreach ($submission->results as $result) {
            $result->calculated_result = $this->submissionCalculatorService->calculateResultFromDeadlines(
                $result,
                $charon->deadlines
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
    private function hasConfirmedSubmission(int $charonId, int $userId)
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
        $charon = $submission->charon;
        if ($charon->gradingMethod->isPreferBest()) {
            return $this->submissionCalculatorService->submissionIsBetterThanLast($submission, $studentId);
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
        $results = $this->submissionsRepository->findResultsByCharonAndGradeType(
            $grademap->charon_id,
            $grademap->grade_type_code
        );

        foreach ($results as $result) {
            if ($this->hasConfirmedSubmission($grademap->charon_id, $result->user_id)) {
                $result = $this->submissionsRepository
                    ->findConfirmedSubmissionsForUserAndCharon($result->user_id, $grademap->charon_id)
                    ->first()
                    ->results()
                    ->where('grade_type_code', $result->grade_type_code)
                    ->where('user_id', $result->user_id)
                    ->first();
            } else {
                $result->calculated_result = $this->submissionCalculatorService->calculateResultFromDeadlines(
                    $result,
                    $grademap->charon->deadlines
                );
                $result->save();
            }

            $this->gradingService->updateGrade(
                $grademap->charon->course,
                $grademap->charon_id,
                $result->grade_type_code,
                $result->user_id,
                $result->calculated_result
            );
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
            ->first();

        if (is_null($studentRegistration)) {
            return;
        }

        $this->defenseRegistrationRepository
            ->updateRegistration($studentRegistration->id, $newProgress, $teacherId, null);
    }
}
