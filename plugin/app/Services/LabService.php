<?php

namespace TTU\Charon\Services;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Repositories\LabTeacherRepository;
use TTU\Charon\Validators\LabValidator;
use Zeizig\Moodle\Models\Course;
use Zeizig\Moodle\Models\User;

/**
 * @version Registration 2.*
 */
class LabService
{
    /** @var CharonRepository */
    private $charonRepository;

    /** @var LabTeacherRepository */
    private $teacherRepository;

    /** @var DefenseRegistrationRepository */
    private $registrationRepository;

    /** @var LabRepository */
    private $labRepository;

    /**
     * @param CharonRepository $charonRepository
     * @param LabTeacherRepository $teacherRepository
     * @param DefenseRegistrationRepository $registrationRepository
     * @param LabRepository $labRepository
     */
    public function __construct(
        CharonRepository $charonRepository,
        LabTeacherRepository $teacherRepository,
        DefenseRegistrationRepository $registrationRepository,
        LabRepository $labRepository
    ) {
        $this->charonRepository = $charonRepository;
        $this->teacherRepository = $teacherRepository;
        $this->registrationRepository = $registrationRepository;
        $this->labRepository = $labRepository;
    }

    /**
     * @param Lab $lab
     * @param Course $course
     * @param array $charonsIds
     * @param array $teacherIds
     * @param array $weeks
     *
     * @return int[]
     * @throws ValidationException
     */
    public function create(Lab $lab, Course $course, array $charonsIds, array $teacherIds, array $weeks): array
    {
        $created = [];

        /** @var Collection|Charon[] $charons */
        $charons = $this->charonRepository->query()->whereIn('id', $charonsIds)->get();
        $teachers = $this->teacherRepository->getTeachersByCourse($course->id);

        $this->validate($lab, $course, $charons, $teacherIds, $teachers);

        $teachers = $teachers->whereIn('id', $teacherIds);
        $labs = $this->collect($lab, $weeks, $course);

        try {
            DB::beginTransaction();

            foreach ($labs as $lab) {
                array_push($created, $this->createOne($lab, $course, $charons, $teachers));
            }

            DB::commit();
        } catch (ValidationException $exception) {
            DB::rollBack();
            Log::warning('Lab creation failed: ' . $exception->getMessage());
            throw $exception;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('Lab creation failed: ' . $exception->getMessage(), $exception->getTrace());
            throw $exception;
        }

        return $created;
    }

    /**
     * @param Lab $lab
     * @param Course $course
     * @param Collection|Charon[] $charons
     * @param Collection|User[] $teachers
     *
     * @return int
     * @throws ValidationException
     */
    private function createOne(Lab $lab, Course $course, Collection $charons, Collection $teachers): int
    {
        $this->validateLab($lab, $course, $charons, $teachers);

        if ($lab->chunk_size < 1) {
            $lab->chunk_size = Config::get('app.defense_chunk_minutes');
        }

        $lab->save();
        $this->attachTeachers($lab, $teachers);
        $this->attachCharons($lab, $charons);
        $this->createRegistrationTimes($lab, $teachers);

        return $lab->id;
    }

    /**
     * @param Lab $lab
     * @param Course $course
     * @param Collection $charons
     * @param array $teacherIds
     * @param Collection $teachers
     *
     * @throws ValidationException
     */
    private function validate(Lab $lab, Course $course, Collection $charons, array $teacherIds, Collection $teachers)
    {
        app()->make(LabValidator::class)
            ->duringCourse($lab, $course)
            ->withMaxDuration($lab)
            ->withCourseCharons($charons, $course->id)
            ->withTeachers($teachers, $teacherIds)
            ->validate();
    }

    /**
     * @param Lab $lab
     * @param array $weeks
     * @param Course $course
     *
     * @return Lab[]
     */
    private function collect(Lab $lab, array $weeks, Course $course): array
    {
        if (empty($weeks)) {
            return [$lab];
        }

        $courseStart = $course->startdate;

        return array_map(function($week) use ($lab, $courseStart) {
            $week = $courseStart->copy()->startOfWeek()->addWeeks($week - 1);
            $offset = $lab->start->copy()->startOfWeek()->diffInWeeks($week, false);

            if ($offset == 0) {
                return $lab;
            }

            /** @var Lab $clone */
            $clone = $lab->replicate();
            $clone->start = $lab->start->copy()->addWeeks($offset);
            $clone->end = $lab->end->copy()->addWeeks($offset);

            return $clone;
        }, $weeks);
    }

    /**
     * @param Lab $lab
     * @param Course $course
     * @param Collection|Charon[] $charons
     * @param Collection|User[] $teachers
     *
     * @throws ValidationException
     */
    private function validateLab(Lab $lab, Course $course, Collection $charons, Collection $teachers)
    {
        app()->make(LabValidator::class)
            ->duringCourse($lab, $course)
            ->withinCharonDeadlines($lab, $charons)
            ->withTeachersAvailable($lab, $teachers)
            ->validate();
    }

    /**
     * @param Lab $lab
     * @param Collection|User[] $teachers
     */
    private function attachTeachers(Lab $lab, Collection $teachers)
    {
        $labTeachers = $teachers->map(function ($teacher) use ($lab) {
            return [
                'lab_id' => $lab->id,
                'teacher_id' => $teacher->id
            ];
        })->all();

        $this->teacherRepository->createMany($labTeachers);
    }

    /**
     * @param Lab $lab
     * @param Collection|Charon[] $charons
     */
    private function attachCharons(Lab $lab, Collection $charons)
    {
        $labCharons = $charons->map(function ($charon) use ($lab) {
            return [
                'lab_id' => $lab->id,
                'charon_id' => $charon->id
            ];
        })->all();

        $this->labRepository->createManyLabCharons($labCharons);
    }

    /**
     * @param Lab $lab
     * @param Collection|User[] $teachers
     */
    private function createRegistrationTimes(Lab $lab, Collection $teachers)
    {
        $timeslotDuration = Config::get('app.defense_timeslot_minutes');
        $registrations = [];

        foreach ($teachers as $teacher) {
            $start = $lab->start->copy();

            while ($start->isBefore($lab->end)) {
                $registrations[] = [
                    'teacher_id' => $teacher->id,
                    'lab_id' => $lab->id,
                    'time' => $start->copy()
                ];
                $start->addMinutes($timeslotDuration);
            }
        }

        $this->registrationRepository->createMany($registrations);
    }

    /**
     * @param Collection $registrationIds
     */
    private function rescheduleRegistrationsIfEmptyGapsAppear(Collection $registrationIds)
    {
        foreach ($registrationIds as $registrationId)
        {
            $registration = $this->registrationRepository->getDefenseRegistrationById($registrationId);
            $lab = $this->labRepository->getLabById($registration->lab_id);

            $chunkSize = $lab->chunk_size;
            $time = $registration->time;

            $multiplier = floor(($registration->time - $lab->start)->getTimestamp() / ($chunkSize * 60));

            $chunkBeginning = $lab->start->addMinutes($chunkSize * $multiplier);
            $chunkEnd = $chunkBeginning->addMinutes($chunkSize);

            $chunkRegistrations = $this->registrationRepository
                ->getRegistrationsByTeacherAndTimeBetween($chunkBeginning, $chunkEnd, $registration->teacher_id);

            for ($i = 0, $i < count($chunkRegistrations); $i++;)
            {
                if (!$this->registrationRepository->isUserBusyAt($registration->student_id, $time, true))
                {
                    break;
                }

                if ($chunkRegistrations[$i]->time > $time)
                {
                    $this->registrationRepository->replaceRegistration(
                        $registration->id, $chunkRegistrations[$i]->student_id, $chunkRegistrations[$i]->charon_id,
                        $chunkRegistrations[$i]->submission_id, $chunkRegistrations[$i]->progress);

                    if ($chunkRegistrations[$i + 1] !== null) // maybe compare count and $i?
                    {
                        $registration->id = $chunkRegistrations[$i]->id;
                        $time = $time + $chunkRegistrations[$i + 1] - $chunkRegistrations[$i];
                    }
                }
            }
        }
    }
}
