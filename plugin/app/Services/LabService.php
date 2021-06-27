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
use TTU\Charon\Models\LabGroup;
use TTU\Charon\Repositories\CharonDefenseLabRepository;
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

    /** @var CharonDefenseLabRepository */
    private $charonDefenseLabRepository;

    /**
     * @param CharonRepository $charonRepository
     * @param LabTeacherRepository $teacherRepository
     * @param DefenseRegistrationRepository $registrationRepository
     * @param LabRepository $labRepository
     * @param CharonDefenseLabRepository $charonDefenseLabRepository
     */
    public function __construct(
        CharonRepository $charonRepository,
        LabTeacherRepository $teacherRepository,
        DefenseRegistrationRepository $registrationRepository,
        LabRepository $labRepository,
        CharonDefenseLabRepository $charonDefenseLabRepository
    ) {
        $this->charonRepository = $charonRepository;
        $this->teacherRepository = $teacherRepository;
        $this->registrationRepository = $registrationRepository;
        $this->labRepository = $labRepository;
        $this->charonDefenseLabRepository = $charonDefenseLabRepository;
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
    public function create(Lab $lab, Course $course, array $charonsIds, array $teacherIds, array $groupIds, array $weeks): array
    {
        $created = [];

        /** @var Collection|Charon[] $charons */
        $charons = $this->charonRepository->query()->whereIn('id', $charonsIds)->get();
        $teachers = $this->teacherRepository->getTeachersByCourse($course->id);

        //TODO: do groups need validation?
        $this->validate($lab, $course, $charons, $teacherIds, $teachers);

        $teachers = $teachers->whereIn('id', $teacherIds);
        $labs = $this->collect($lab, $weeks, $course);

        try {
            DB::beginTransaction();

            foreach ($labs as $lab) {
                array_push($created, $this->createOne($lab, $course, $charons, $teachers, $groupIds));
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
     * @param array $charonsIds
     * @param array $teacherIds
     * @param array $groupIds
     *
     * @return Lab
     * @throws ValidationException
     */
    public function update(Lab $lab, Course $course, array $charonsIds, array $teacherIds, array $groupIds): Lab
    {
        $charons = $this->charonRepository->query()->whereIn('id', $charonsIds)->get();
        $teachers = $this->teacherRepository->getTeachersByCourse($course->id);

        $this->validate($lab, $course, $charons, $teacherIds, $teachers);

        $teachers = $teachers->whereIn('id', $teacherIds);
        $this->validateLab($lab, $course, $charons, $teachers);

        if ($lab->chunk_size < 1) {
            $lab->chunk_size = Config::get('app.defense_chunk_minutes');
        }

        try {
            DB::beginTransaction();
            
            $lab->save();

            $oldTeacherIds = $this->teacherRepository
                ->getTeachersByLabAndCourse($course->id, $lab->id)
                ->pluck('id')
                ->toArray();
            $teachersToBeRemoved = array_diff($oldTeacherIds, $teacherIds);
            $toBeAdded = array_diff($teacherIds, $oldTeacherIds);
            $this->attachTeachers($lab, collect($toBeAdded));
            foreach ($teachersToBeRemoved as $id) {
                $this->teacherRepository->deleteByLabAndTeacherId($lab->id, $id);
            }

            $oldCharonIds = $this->labRepository
                ->getCharonsForLab($course->id, $lab->id)
                ->pluck('id')
                ->toArray();
            $charonsToBeRemoved = array_diff($oldCharonIds, $charonsIds);
            $toBeAdded = array_diff($charonsIds, $oldCharonIds);
            $this->attachCharons($lab, collect($toBeAdded));
            foreach ($charonsToBeRemoved as $id) {
                $this->charonDefenseLabRepository->deleteDefenseLabByLabAndCharon($lab->id, $id);
            }

            $oldGroupIds = $this->labRepository
                ->getGroupsForLab($course->id, $lab->id)
                ->pluck('id')
                ->toArray();
            $groupsToBeRemoved = array_diff($oldGroupIds, $groupIds);
            $toBeAdded = array_diff($groupIds, $oldGroupIds);
            $this->attachGroups($lab, collect($toBeAdded));
            foreach ($groupsToBeRemoved as $id) {
                $this->labRepository->deleteGroupForLab($lab->id, $id);
            }

            $this->labRepository->countRegistrations($lab->id, $lab->start, $lab->end, $charonsToBeRemoved, $teachersToBeRemoved, true);

            DB::commit();
        } catch (ValidationException $exception) {
            DB::rollBack();
            Log::warning('Lab update failed: ' . $exception->getMessage());
            throw $exception;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('Lab update failed: ' . $exception->getMessage(), $exception->getTrace());
            throw $exception;
        }

        return $lab;
    }

    /**
     * @param Lab $lab
     * @param Course $course
     * @param Collection|Charon[] $charons
     * @param Collection|User[] $teachers
     * @param array $groupIds
     *
     * @return int
     * @throws ValidationException
     */
    private function createOne(Lab $lab, Course $course, Collection $charons, Collection $teachers, array $groupIds): int
    {
        $this->validateLab($lab, $course, $charons, $teachers);

        if ($lab->chunk_size < 1) {
            $lab->chunk_size = Config::get('app.defense_chunk_minutes');
        }

        $lab->save();
        $this->attachTeachers($lab, $teachers->pluck('id'));
        $this->attachCharons($lab, $charons->pluck('id'));
        $this->attachGroups($lab, collect($groupIds));
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
     * @param Collection $teacherIds
     */
    private function attachTeachers(Lab $lab, Collection $teacherIds)
    {
        $labTeachers = $teacherIds->map(function ($teacherId) use ($lab) {
            return [
                'lab_id' => $lab->id,
                'teacher_id' => $teacherId
            ];
        })->all();

        $this->teacherRepository->createMany($labTeachers);
    }

    /**
     * @param Lab $lab
     * @param Collection $charonIds
     */
    private function attachCharons(Lab $lab, Collection $charonIds)
    {
        $labCharons = $charonIds->map(function ($charonId) use ($lab) {
            return [
                'lab_id' => $lab->id,
                'charon_id' => $charonId
            ];
        })->all();

        $this->labRepository->createManyLabCharons($labCharons);
    }

    /**
     * @param Lab $lab
     * @param Collection $groupIds
     */
    private function attachGroups(Lab $lab, Collection $groupIds)
    {
        $labGroups = $groupIds->map(function ($groupId) use ($lab) {
            return [
                'lab_id' => $lab->id,
                'group_id' => $groupId
            ];
        })->all();

        $this->labRepository->createManyLabGroups($labGroups);
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
}
