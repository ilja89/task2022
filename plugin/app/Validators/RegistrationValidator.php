<?php

namespace TTU\Charon\Validators;

use Illuminate\Database\Eloquent\Collection;
use TTU\Charon\Models\Submission;
use Zeizig\Moodle\Models\Course;

/**
 * @version Registration 2.*
 */
class RegistrationValidator extends WithErrors
{
    /**
     * @param Course $course
     * @param int $studentId
     * @return $this
     */
    public function studentBelongsToCourse(Course $course, int $studentId): RegistrationValidator
    {
        $this->after(function () use ($course, $studentId) {
            // TODO:
        });

        return $this;
    }

    /**
     * @param Course $course
     * @param Collection|Submission[] $submissions
     * @return $this
     */
    public function submissionsBelongToCourse(Course $course, Collection $submissions): RegistrationValidator
    {
        $this->after(function () use ($course, $submissions) {
            foreach ($submissions as $submission) {
                if ($submission->charon->course != $course->id) {
                    $this->addError(
                        'submissions',
                        'Submission %d does not belong to course %d',
                        $submission->id, $course->id
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
