<?php

namespace TTU\Charon\Validators;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Eloquent\Collection;
use TTU\Charon\Facades\MoodleConfig;
use TTU\Charon\Models\Submission;
use TTU\Charon\Repositories\LabTeacherRepository;
use TTU\Charon\Repositories\StudentsRepository;
use Zeizig\Moodle\Globals\User;

/**
 * @version Registration 2.*
 */
class RegistrationValidator extends WithErrors
{

    /** @var MoodleConfig */
    protected $moodleConfig;


    /**
     * @param Translator $translator
     * @param MoodleConfig $moodleConfig
     */
    public function __construct(
        Translator $translator,
        MoodleConfig $moodleConfig
    )
    {
        parent::__construct($translator, [], []);
        $this->moodleConfig = $moodleConfig;
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

    public function checkCurrentUsersValidityForRegisteringDefence($userId, $courseId): RegistrationValidator
    {
        $teacherRepository = new LabTeacherRepository($this->moodleConfig);
        $studentsRepository = new StudentsRepository();
        $currentUserId = (new User)->currentUserId();
        $teachers = $teacherRepository->getTeachersByCourseId($courseId);

        $teacherIds = array(count($teachers));
        foreach ($teachers as $teacher)
        {
            array_push($teacherIds, $teacher->id);
        }

        $students = $studentsRepository->searchStudentsByCourseAndKeyword($courseId, $currentUserId);
        $studentId = null;

        if (count($students) > 0)
        {
            $studentId = $students[0];
        }
        if ($currentUserId != $userId && $studentId != $userId || !in_array($currentUserId, $teacherIds) )
        {
            $this->addError(
                'current user',
                'Current user %d is not authorized to register defenses to other users',
                $currentUserId
            );
        }

        return $this;
    }
}
