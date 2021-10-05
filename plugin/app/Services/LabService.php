<?php

namespace TTU\Charon\Services;

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
     *    - estimatedStartTime: shows estimated time when student registering on this lab will be defending
     *    - id: id of this lab
     *    - name: name of this lab
     *
     * @param int $charonId
     * @return mixed
     */
    public function getLabsWithCapacityInfoForCharon(int $charonId)
    {
        $labs = $this->labRepository->getLabsByCharonIdLaterEqualToday($charonId);

        // Get length of this charon
        $charonLength = $this->charonRepository->getCharonById($charonId)->defense_duration;

        // Calculate lab capacity
        // Calculate avg defense length and check if lab can be booked
        foreach ($labs as $lab) {

            // Get teachers number
            $teacherNum = $this->teacherRepository->countLabTeachers($lab->id);

            // Calculate lab capacity
            $capacity = ((strtotime($lab->end) - strtotime($lab->start)) / 60) * $teacherNum;

            // Get all defense durations
            $defenceTimes = $this->defenseRegistrationRepository->getDefenseRegistrationDurationsByLab($lab->id);

            // Get sum of defence times and divide to get avg
            $defenceTimesSum = 0;
            foreach ($defenceTimes as $time) {
                $defenceTimesSum += $time->defense_duration;
            }

            if ($capacity - $defenceTimesSum > $charonLength) {
                $averageWaitingTime = ($defenceTimesSum / $teacherNum) * 60;
                $lab->estimated_start_time = date("Y-m-d H:i:s", strtotime($lab->start) + $averageWaitingTime);
            } else {
                $lab->estimated_start_time = null;
            }
        }

        return $labs;
    }
}
