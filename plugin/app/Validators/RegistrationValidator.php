<?php

namespace TTU\Charon\Validators;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Eloquent\Collection;
use TTU\Charon\Models\Submission;

/**
 * @version Registration 2.*
 */
class RegistrationValidator extends WithErrors
{
    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator) {
        parent::__construct($translator, [], []);
    }

    /**
     * @param int $courseId
     * @param int $studentId
     * @return $this
     */
    public function studentBelongsToCourse(int $courseId, int $studentId): RegistrationValidator
    {
        $this->after(function () use ($courseId, $studentId) {
            /**
             * TODO:
             * check exists from role_assignments
             * join role_assignments.contextid on context.id
             * where role_assignments.userid = $studentId
             * and context.instanceid = $courseId
             */
        });

        return $this;
    }

    /**
     * @param int $courseId
     * @param \Illuminate\Support\Collection|Submission[] $submissions
     * @return $this
     */
    public function submissionsBelongToCourse(int $courseId, \Illuminate\Support\Collection $submissions): RegistrationValidator
    {
        $this->after(function () use ($courseId, $submissions) {
            foreach ($submissions as $submission) {
                if ($submission->charon->course != $courseId) {
                    $this->addError(
                        'submissions',
                        'Submission %d does not belong to course %d',
                        $submission->id, $courseId
                    );
                }
            }
        });

        return $this;
    }

    /**
     * @param int $studentId
     * @param \Illuminate\Support\Collection|Submission[] $submissions
     * @return $this
     */
    public function submissionsBelongToStudent(int $studentId, \Illuminate\Support\Collection $submissions): RegistrationValidator
    {
        $this->after(function () use ($studentId, $submissions) {
            foreach ($submissions as $submission) {
                if (!$submission->users->pluck('id')->contains($studentId)) {
                    $this->addError(
                        'submissions',
                        'Submission %d does not belong to student %d',
                        $submission->id, $studentId
                    );
                }
            }
        });

        return $this;
    }
}
