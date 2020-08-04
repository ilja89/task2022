<?php

namespace TTU\Charon\Repositories;

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
            ->get();
        return $defenseRegistrations;
    }

}
