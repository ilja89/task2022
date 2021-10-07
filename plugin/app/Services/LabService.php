<?php

namespace TTU\Charon\Services;

use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Repositories\LabTeacherRepository;

class LabService
{
    /** @var CharonRepository */
    private $charonRepository;

    /** @var LabRepository */
    private $labRepository;

    /** @var LabTeacherRepository */
    private $teacherRepository;

    /** @var DefenseRegistrationRepository */
    private $defenseRegistrationRepository;

    /**
     * LabService constructor.
     * @param CharonRepository $charonRepository
     * @param LabTeacherRepository $teacherRepository
     * @param DefenseRegistrationRepository $defenseRegistrationRepository
     * @param LabRepository $labRepository
     */
    public function __construct(
        CharonRepository $charonRepository,
        LabTeacherRepository $teacherRepository,
        DefenseRegistrationRepository $defenseRegistrationRepository,
        LabRepository $labRepository
    ) {
        $this->charonRepository = $charonRepository;
        $this->teacherRepository = $teacherRepository;
        $this->defenseRegistrationRepository = $defenseRegistrationRepository;
        $this->labRepository = $labRepository;
    }

    /**
     * Function what will return list of labs related to charon got by $charonId, with following fields:
     *    - course_id: shows id of course what lab is related to
     *    - defenders_num: shows number of existing registrations for this lab
     *    - start: shows lab end time
     *    - end: shows lab end time
     *    - estimated_start_time: shows estimated time when student registering on this lab will be defending
     *    - id: id of this lab
     *    - name: name of this lab
     *
     * @param int $charonId
     *
     * @return Lab[]
     */
    public function getLabsWithCapacityInfoForCharon(int $charonId): array
    {
        $labs = $this->labRepository->getLabsByCharonIdLaterEqualToday($charonId);

        // Get length of given charon
        $charonLength = $this->charonRepository->getCharonById($charonId)->defense_duration;

        // Calculate lab capacity and the shortest defence time the registration would have
        foreach ($labs as $lab) {

            // Get teachers number
            $teacherNum = $this->teacherRepository->countLabTeachers($lab->id);

            // Calculate lab capacity
            $capacity = ((strtotime($lab->end) - strtotime($lab->start)) / 60);

            // Get all defense durations
            $defenceTimes = $this->defenseRegistrationRepository->getDefenseRegistrationDurationsByLab($lab->id);

            $queuePresumption = array_fill(0, $teacherNum, 0);

            foreach ($defenceTimes as $defenceTime) {
                $queuePresumption[array_keys($queuePresumption, min($queuePresumption))[0]] +=
                    $defenceTime->defense_duration;
            }

            $shortestWaitingTime = $queuePresumption[array_keys($queuePresumption, min($queuePresumption))[0]];

            if ($capacity >= $shortestWaitingTime + $charonLength) {
                $lab["estimated_start_time"] = $lab->start->addMinutes($shortestWaitingTime);
            } else {
                $lab["estimated_start_time"] = null;
            }
        }

        return $labs;
    }
}
