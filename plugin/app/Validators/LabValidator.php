<?php

namespace TTU\Charon\Validators;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\User;


/**
 * @version Registration 2.*
 */
class LabValidator extends Validator
{
    const MAX_LAB_DURATION_HOURS = 24;

    /** @var CharonRepository */
    private $charonRepository;

    /** @var DefenseRegistrationRepository */
    private $registrationRepository;

    /**
     * @param Translator $translator
     * @param CharonRepository $charonRepository
     * @param DefenseRegistrationRepository $registrationRepository
     */
    public function __construct(
        Translator $translator,
        CharonRepository $charonRepository,
        DefenseRegistrationRepository $registrationRepository
    ) {
        parent::__construct($translator, [], []);
        $this->charonRepository = $charonRepository;
        $this->registrationRepository = $registrationRepository;
    }

    public function withMaxDuration(Lab $lab): LabValidator
    {
        $this->after(function () use ($lab) {
            if ($lab->start->diffInRealHours($lab->end) > self::MAX_LAB_DURATION_HOURS) {
                $this->addError(
                    'end',
                    'Lab duration %dh is longer than allowed %dh',
                    $lab->start->diffInRealHours($lab->end), self::MAX_LAB_DURATION_HOURS
                );
            }
        });

        return $this;
    }

    /**
     * @param Lab $lab
     * @param Course $course
     *
     * @return $this
     */
    public function duringCourse(Lab $lab, Course $course): LabValidator
    {
        $this->data = $this->data + $this->parseData([
            'start' => $lab->start,
            'end' => $lab->end
        ]);

        $this->addRules([
            'start' => 'before:' . $course->enddate . '|after:' . $course->startdate,
            'end' => 'before:' . $course->enddate . '|after:' . $course->startdate
        ]);

        $this->setCustomMessages([
            'start.before' => sprintf('Lab should start (%s) before the Course ends (%s)', $lab->start, $course->enddate),
            'start.after' => sprintf('Lab should start (%s) after the Course has started (%s)', $lab->start, $course->startdate),
            'end.before' => sprintf('Lab should end (%s) before the Course ends (%s)', $lab->end, $course->enddate),
            'end.after' => sprintf('Lab should not end (%s) before the Course has started (%s)', $lab->end, $course->startdate)
        ]);

        return $this;
    }

    /**
     * @param Collection|Charon[] $charons
     * @param int $courseId
     *
     * @return $this
     */
    public function withCourseCharons(Collection $charons, int $courseId): LabValidator
    {
        $this->after(function () use ($charons, $courseId) {
            foreach ($charons as $charon) {
                if ($charon->course != $courseId) {
                    $this->addError('charons', 'Charon %s does not belong to this Course', $charon->name);
                }
            }
        });

        return $this;
    }

    /**
     * @param Collection|User $teachers
     * @param array $users
     *
     * @return $this
     */
    public function withTeachers(Collection $teachers, array $users): LabValidator
    {
        $courseTeachers = $teachers->pluck('id')->values()->all();

        $this->after(function () use ($courseTeachers, $users) {
            foreach ($users as $user) {
                if (!in_array($user, $courseTeachers)) {
                    $this->addError('teachers', 'User with ID %d is not a Teacher for this Course', $user);
                }
            }
        });

        return $this;
    }

    /**
     * @param Lab $lab
     * @param Collection|Charon[] $charons
     *
     * @return $this
     */
    public function withinCharonDeadlines(Lab $lab, Collection $charons): LabValidator
    {
        $this->after(function () use ($lab, $charons) {
            foreach ($charons as $charon) {
                if ($charon->defense_start_time && $charon->defense_start_time->isAfter($lab->end)) {
                    $this->addError(
                        'charons',
                        'Charon %s defense start time (%s) is after the Lab end time (%s)',
                        $charon->name, $charon->defense_start_time, $lab->end
                    );
                }

                if ($charon->defense_deadline && $charon->defense_deadline->isBefore($lab->start)) {
                    $this->addError(
                        'charons',
                        'Charon %s defense deadline (%s) is before Lab start time (%s)',
                        $charon->name, $charon->defense_deadline, $lab->start
                    );
                }
            }
        });

        return $this;
    }

    /**
     * @param Lab $lab
     * @param Collection|User[] $teachers
     *
     * @return $this
     */
    public function withTeachersAvailable(Lab $lab, Collection $teachers): LabValidator
    {
        $this->after(function () use ($lab, $teachers) {
            $busy = $this->registrationRepository->checkBusyTeachersBetween($lab->start, $lab->end, $lab->id);

            foreach ($teachers as $teacher) {
                if (in_array($teacher->id, $busy)) {
                    $this->addError(
                        'teachers',
                        'Teacher %s %s is busy during Lab ranging from %s to %s',
                        $teacher->firstname, $teacher->lastname, $lab->start, $lab->end
                    );
                }
            }
        });

        // TODO: check if busy at previous 1.* Registration flow lab?

        // TODO: check if busy at Moodle calendar event?

        return $this;
    }

    /**
     * @param string $field
     * @param string $message
     * @param mixed ...$params
     */
    private function addError(string $field, string $message, ...$params)
    {
        $this->errors()->add($field, sprintf($message, ...$params));
    }
}
