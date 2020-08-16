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
     * LabRepository constructor.
     *
     * @param ModuleService $moduleService
     */
    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    /**
     * Get defense registrations by course.
     * @param $courseId
     * @return Collection|Defenders[]
     */
    public function getDefenseRegistrationsByCourse($courseId) {
        $defenseRegistrations = \DB::table('defenders')
            ->join('charon_submission', 'charon_submission.id', 'defenders.submission_id')
            ->join('charon', 'charon.id', 'charon_submission.charon_id')
            ->where('charon.course', $courseId)
            ->select('defenders.id', 'defenders.choosen_time', 'defenders.student_id', 'defenders.student_name',
                'charon.defense_duration', 'defenders.my_teacher', 'defenders.submission_id', 'defenders.progress')
            ->orderBy('defenders.choosen_time')
            ->get();
        return $defenseRegistrations;
    }

    /**
     * Get defense registrations by course, filtered by after and before date.
     * @param $courseId
     * @param $after
     * @param $before
     * @return Collection|Defenders[]
     */
    public function getDefenseRegistrationsByCourseFiltered($courseId, $after, $before) {
        if ($after != 'null' && $before != 'null') {
            $filteringWhere = "choosen_time BETWEEN '" . Carbon::parse($after)->format('Y-m-d H:i:s') . "' AND '" . Carbon::parse($before)->format('Y-m-d H:i:s') . "'";
        } elseif ($after != 'null') {
            $filteringWhere = "choosen_time >= '" . Carbon::parse($after)->format('Y-m-d H:i:s') . "'";
        } elseif ($before != 'null') {
            $filteringWhere = "choosen_time <= '" . Carbon::parse($before)->format('Y-m-d H:i:s') . "'";
        } else {
            return $this->getDefenseRegistrationsByCourse($courseId);
        }
        $defenseRegistrations = \DB::table('defenders')
            ->join('charon_submission', 'charon_submission.id', 'defenders.submission_id')
            ->join('charon', 'charon.id', 'charon_submission.charon_id')
            ->where('charon.course', $courseId)
            ->whereRaw($filteringWhere)
            ->select('defenders.id', 'defenders.choosen_time', 'defenders.student_id', 'defenders.student_name',
                'charon.defense_duration', 'defenders.my_teacher', 'defenders.submission_id', 'defenders.progress')
            ->orderBy('defenders.choosen_time')
            ->get();
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
            $eh = \DB::table('defenders')
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
