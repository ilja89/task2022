<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Collection;

use TTU\Charon\Models\Defenders;
use Zeizig\Moodle\Services\ModuleService;

/**
 * Class CharonRepository.
 * Used to handle database actions.
 *
 * @package TTU\Charon\Repositories
 */
class DefenseRegistrationRepository
{
    /** @var ModuleService */
    protected $moduleService;
    /**
     * @var LabTeacherRepository
     */
    protected $labTeacherRepository;

    /**
     * LabRepository constructor.
     *
     * @param ModuleService $moduleService
     * @param LabTeacherRepository $labTeacherRepository
     */
    public function __construct(ModuleService $moduleService, LabTeacherRepository $labTeacherRepository)
    {
        $this->moduleService = $moduleService;
        $this->labTeacherRepository = $labTeacherRepository;
    }

    /**
     * Get defense registrations by course.
     * @param $courseId
     * @return Collection|Defenders[]
     */
    public function getDefenseRegistrationsByCourse($courseId) {
        $defenseRegistrations = \DB::table('charon_defenders')
            ->join('charon_submission', 'charon_submission.id', 'charon_defenders.submission_id')
            ->join('charon', 'charon.id', 'charon_submission.charon_id')
            ->where('charon.course', $courseId)
            ->select('charon_defenders.id', 'charon_defenders.choosen_time', 'charon_defenders.student_id', 'charon_defenders.student_name',
                'charon.defense_duration', 'charon_defenders.my_teacher', 'charon_defenders.submission_id', 'charon_defenders.progress',
                'charon_defenders.teacher_id')
            ->orderBy('charon_defenders.choosen_time')
            ->get();
        for ($i = 0; $i < count($defenseRegistrations); $i++) {
            $defenseRegistrations[$i]->teacher = $this->labTeacherRepository->getTeacherByUserId($defenseRegistrations[$i]->teacher_id)[0];
        }
        return $defenseRegistrations;
    }

    /**
     * Get defense registrations by course, filtered by after and before date.
     * @param $courseId
     * @param $after
     * @param $before
     * @param $teacher_id
     * @param $progress
     * @return Collection|Defenders[]
     */
    public function getDefenseRegistrationsByCourseFiltered($courseId, $after, $before, $teacher_id, $progress) {
        if ($after != 'null' && $before != 'null') {
            $filteringWhere = "choosen_time BETWEEN '" . Carbon::parse($after)->format('Y-m-d H:i:s') . "' AND '" . Carbon::parse($before)->format('Y-m-d H:i:s') . "'";
        } elseif ($after != 'null') {
            $filteringWhere = "choosen_time >= '" . Carbon::parse($after)->format('Y-m-d H:i:s') . "'";
        } elseif ($before != 'null') {
            $filteringWhere = "choosen_time <= '" . Carbon::parse($before)->format('Y-m-d H:i:s') . "'";
        } else {
            $filteringWhere = "student_id > '-1'";
        }
        $teacher_filter = "student_id > '-1'";
        if ($teacher_id != -1) {
            $teacher_filter = "teacher_id LIKE '" . $teacher_id . "'";
        }
        $progress_filter = "student_id > '-1'";
        if ($progress != 'null') {
            $progress_filter = "progress LIKE '" . $progress . "'";
        }
        $defenseRegistrations = \DB::table('charon_defenders')
            ->join('charon_submission', 'charon_submission.id', 'charon_defenders.submission_id')
            ->join('charon', 'charon.id', 'charon_submission.charon_id')
            ->where('charon.course', $courseId)
            ->whereRaw($filteringWhere)
            ->whereRaw($teacher_filter)
            ->whereRaw($progress_filter)
            ->select('charon_defenders.id', 'charon_defenders.choosen_time', 'charon_defenders.student_id', 'charon_defenders.student_name',
                'charon.defense_duration', 'charon_defenders.my_teacher', 'charon_defenders.submission_id', 'charon_defenders.progress',
                'charon_defenders.teacher_id')
            ->orderBy('charon_defenders.choosen_time')
            ->get();
        for ($i = 0; $i < count($defenseRegistrations); $i++) {
            $defenseRegistrations[$i]->teacher = $this->labTeacherRepository->getTeacherByUserId($defenseRegistrations[$i]->teacher_id)[0];
        }
        return $defenseRegistrations;
    }

    /**
     * Save defending progress.
     * @param $defenseId
     * @param $newProgress
     * @return Defenders
     */
    public function saveProgress($defenseId, $newProgress) {
        $defense = Defenders::find($defenseId);
        $defense->progress = $newProgress;
        $defense->save();
        return $defense;
    }

    /**
     * Save defense progress by student id.
     * @param $charonId
     * @param $studentId
     * @param $newProgress
     * @return Defenders
     */
    public function saveProgressByStudentId($charonId, $studentId, $newProgress) {
        try {
            $eh = \DB::table('charon_defenders')
                ->where('student_id', $studentId)
                ->where('charon_id', $charonId)
                ->select('*')
                ->get();
            $defense = $eh[0];
            return $this->saveProgress($defense->id, $newProgress);
        } catch (\Exception $e) {  // try-catch because that means there is no row in defenders table
                                    // associated with this student and charon

        }
    }

}
