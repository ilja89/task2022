<?php

namespace TTU\Charon\Validators;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Collection;
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
            // Implement at: https://gitlab.cs.ttu.ee/ained/charon/-/issues/468
        });

        return $this;
    }

    /**
     * @param int $courseId
     * @param Collection|Submission[] $submissions
     * @return $this
     */
    public function submissionsBelongToCourse(int $courseId, Collection $submissions): RegistrationValidator
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
     * @param Collection|Submission[] $submissions
     * @return $this
     */
    public function submissionsBelongToStudent(int $studentId, Collection $submissions): RegistrationValidator
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
