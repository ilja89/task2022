<?php

namespace TTU\Charon\Services;

use TTU\Charon\Helpers\SubmissionCalculator;
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

    /** @var SubmissionCalculator */
    private $submissionCalculator;

    /**
     * CharonGradingService constructor.
     *
     * @param GradingService $gradingService
     * @param GrademapService $grademapService
     * @param CharonRepository $charonRepository
     * @param SubmissionsRepository $submissionsRepository
     * @param SubmissionCalculator $submissionCalculator
     */
    public function __construct(
        GradingService $gradingService,
        GrademapService $grademapService,
        CharonRepository $charonRepository,
        SubmissionsRepository $submissionsRepository,
        SubmissionCalculator $submissionCalculator
    ) {
        $this->gradingService        = $gradingService;
        $this->grademapService       = $grademapService;
        $this->charonRepository      = $charonRepository;
        $this->submissionsRepository = $submissionsRepository;
        $this->submissionCalculator = $submissionCalculator;
    }

    /**
     * Update the grade for the user if it should be updated.
     *
     * @param  Submission $submission
     *
     * @param bool $force
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
     * @param  Submission $submission
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
     * @param  Submission $submission
     * @param  bool $force
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
     * @param Submission $submission
     *
     * @return void
     */
    public function calculateCalculatedResultsForNewSubmission(Submission $submission)
    {
        $charon = $submission->charon;
        foreach ($submission->results as $result) {
            $result->calculated_result = $this->submissionCalculator->calculateResultFromDeadlines($result, $charon->deadlines);
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
     * @param  Submission $submission
     *
     * @return bool
     */
    private function shouldUpdateBasedOnGradingMethod(Submission $submission)
    {
        $charon = $submission->charon;
        if ($charon->gradingMethod->isPreferBest()) {
            return $this->submissionCalculator->submissionIsBetterThanLast($submission);
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
        // TODO: Charon_id does not exist in results table
        $results = Result::whereHas('submission', function ($query) use ($grademap) {
            $query->where('charon_id', $grademap->charon_id);
        })
            ->where('grade_type_code', $grademap->grade_type_code)
            ->get();
        $deadlines = $grademap->charon->deadlines;
        $courseId = $grademap->charon->course;

        $results->each(function ($result) use ($grademap, $deadlines, $courseId) {
            /** @var Result $result */
            if (!$this->hasConfirmedSubmission($grademap->charon_id, $result->submission->user_id)) {
                $result->calculated_result = $this->submissionCalculator->calculateResultFromDeadlines($result, $deadlines);
                $result->save();

                $this->gradingService->updateGrade(
                    $courseId,
                    $grademap->charon_id,
                    $result->grade_type_code,
                    $result->submission->user_id,
                    $result->calculated_result
                );
            }
        });
    }
}
