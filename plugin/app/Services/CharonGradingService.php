<?php

namespace TTU\Charon\Services;

use TTU\Charon\Models\Grademap;
use TTU\Charon\Models\Result;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\CharonRepository;
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
    /** @var GrademapService */
    private $grademapService;
    /** @var CharonRepository */
    private $charonRepository;
    /** @var SubmissionsRepository */
    private $submissionsRepository;
    /** @var SubmissionCalculatorService */
    private $submissionCalculatorService;

    /**
     * CharonGradingService constructor.
     *
     * @param  GradingService  $gradingService
     * @param  GrademapService  $grademapService
     * @param  CharonRepository  $charonRepository
     * @param  SubmissionsRepository  $submissionsRepository
     * @param  SubmissionCalculatorService  $submissionCalculatorService
     */
    public function __construct(
        GradingService $gradingService,
        GrademapService $grademapService,
        CharonRepository $charonRepository,
        SubmissionsRepository $submissionsRepository,
        SubmissionCalculatorService $submissionCalculatorService
    ) {
        $this->gradingService              = $gradingService;
        $this->grademapService             = $grademapService;
        $this->charonRepository            = $charonRepository;
        $this->submissionsRepository       = $submissionsRepository;
        $this->submissionCalculatorService = $submissionCalculatorService;
    }

    /**
     * Update the grade for the user if it should be updated.
     *
     * @param  Submission  $submission
     * @param  bool  $force
     *
     * @return void
     */
    public function updateGradeIfApplicable($submission, $force = false)
    {
        if ( ! $this->gradesShouldBeUpdated($submission, $force)) {
            return;
        }

        $charon = $submission->charon;
        // TODO: Send commit hash info to tester! Grade will now be updated!
        $courseId = $charon->courseModule()->course;

        $gradeTypeCodes = $charon->getGradeTypeCodes();
        foreach ($submission->results as $result) {
            if ( ! $gradeTypeCodes->contains($result->grade_type_code)) {
                continue;
            }

            $this->gradingService->updateGrade(
                $courseId,
                $charon->id,
                $result->grade_type_code,
                $submission->user_id,
                $result->calculated_result
            );
        }
    }

    /**
     * Confirms the given submission and unconfirms the rest for the user.
     *
     * @param  Submission  $submission
     *
     * @return void
     */
    public function confirmSubmission($submission)
    {
        $userId      = $submission->user_id;
        $submissions = $this->submissionsRepository->findConfirmedSubmissionsForUserAndCharon($userId,
            $submission->charon_id);

        foreach ($submissions as $confirmedSubmission) {
            if ($submission->id === $confirmedSubmission->id) {
                continue;
            }

            $this->submissionsRepository->unconfirmSubmission($confirmedSubmission);
        }

        $this->submissionsRepository->confirmSubmission($submission);
    }

    /**
     * Check if the given submission should update grades.
     *
     * @param  Submission  $submission
     * @param  bool  $force
     *
     * @return bool
     */
    public function gradesShouldBeUpdated(Submission $submission, $force)
    {
        if ($force) {
            return true;
        }

        if ($this->hasConfirmedSubmission($submission->charon_id, $submission->user_id)) {
            return false;
        }

        return $this->shouldUpdateBasedOnGradingMethod($submission);
    }

    /**
     * Calculates the calculated results for given new submission and
     * saves them.
     *
     * @param  Submission  $submission
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
     * @param  int  $charonId
     * @param  int  $userId
     *
     * @return bool
     */
    private function hasConfirmedSubmission($charonId, $userId)
    {
        return $this->submissionsRepository->charonHasConfirmedSubmissions(
            $charonId, $userId
        );
    }

    /**
     * Check if the submission should be updated based on the grading
     * method of the charon.
     *
     * @param  Submission  $submission
     *
     * @return bool
     */
    private function shouldUpdateBasedOnGradingMethod(Submission $submission)
    {
        $charon = $submission->charon;
        if ($charon->gradingMethod->isPreferBest()) {
            return $this->submissionCalculatorService->submissionIsBetterThanLast($submission);
        }

        return true;
    }

    /**
     * Recalculate grades for the given grademap.
     *
     * @param  Grademap  $grademap
     *
     * @return void
     */
    public function recalculateGrades(Grademap $grademap)
    {
        $results = $this->submissionsRepository->findResultsByCharonAndGradeType(
            $grademap->charon_id,
            $grademap->grade_type_code
        );

        $results->each(function ($result) use ($grademap) {
            /** @var Result $result */
            if ( ! $this->hasConfirmedSubmission($grademap->charon_id, $result->submission->user_id)) {
                $result->calculated_result = $this->submissionCalculatorService->calculateResultFromDeadlines(
                    $result, $grademap->charon->deadlines
                );
                $result->save();
            } else {
                $result = $this->submissionsRepository->findConfirmedSubmissionsForUserAndCharon(
                    $result->submission->user_id, $grademap->charon_id
                )->first()->results()->where('grade_type_code', $result->grade_type_code)->first();
            }

            $this->gradingService->updateGrade(
                $grademap->charon->course,
                $grademap->charon_id,
                $result->grade_type_code,
                $result->submission->user_id,
                $result->calculated_result
            );
        });
    }
}
