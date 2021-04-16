<?php

namespace TTU\Charon\Services;

use Exception;
use Illuminate\Support\Collection;
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
    const TIMESLOT_INTERVAL_MINUTES = 5;
    const DEFAULT_CHUNK_SIZE_MINUTES = 30;

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

        if ($lab->chunk_size == 0) {
            $lab->chunk_size = self::DEFAULT_CHUNK_SIZE_MINUTES;
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

    private function createRegistrationTimes(Lab $lab, Collection $teachers)
    {
        $registrations = [];

        foreach ($teachers as $teacher) {
            $start = $lab->start->copy();

            while ($start->isBefore($lab->end)) {
                $registrations[] = [
                    'teacher_id' => $teacher->id,
                    'lab_id' => $lab->id,
                    'time' => $start->copy()
                ];

                $start->addMinutes(self::TIMESLOT_INTERVAL_MINUTES);
            }
        }

        $this->registrationRepository->createMany($registrations);
    }
}
