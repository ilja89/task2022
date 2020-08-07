<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
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

    public function getDefenseRegistrationsByCourse($courseId) {
        $defenseRegistrations = \DB::table('defenders')
            ->join('charon_submission', 'charon_submission.id', 'defenders.submission_id')
            ->join('charon', 'charon.id', 'charon_submission.charon_id')
            ->where('charon.course', $courseId)
            ->select('defenders.choosen_time', 'defenders.student_name', 'charon.defense_duration', 'defenders.my_teacher', 'defenders.submission_id')
            ->orderBy('defenders.choosen_time')
            ->get();
        return $defenseRegistrations;
    }

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
            ->select('defenders.choosen_time', 'defenders.student_name', 'charon.defense_duration', 'defenders.my_teacher', 'defenders.submission_id')
            ->orderBy('defenders.choosen_time')
            ->get();
        return $defenseRegistrations;
    }

}
