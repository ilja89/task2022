<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Repositories\LabTeacherRepository;
use Zeizig\Moodle\Models\User;

class LabService
{
    /** @var DefenseRegistrationRepository */
    private $defenseRegistrationRepository;

    /** @var LabTeacherRepository */
    private $labTeacherRepository;

    /** @var LabRepository */
    private $labRepository;

    /** @var CharonRepository */
    private $charonRepository;

    /**
     * LabService constructor.
     *
     * @param DefenseRegistrationRepository $defenseRegistrationRepository
     * @param LabTeacherRepository $labTeacherRepository
     * @param LabRepository $labRepository,
     * @param CharonRepository $charonRepository
     */
    public function __construct(
        DefenseRegistrationRepository $defenseRegistrationRepository,
        LabTeacherRepository $labTeacherRepository,
        LabRepository $labRepository,
        CharonRepository $charonRepository
    ) {
        $this->defenseRegistrationRepository = $defenseRegistrationRepository;
        $this->labTeacherRepository = $labTeacherRepository;
        $this->labRepository = $labRepository;
        $this->charonRepository = $charonRepository;
    }

    /**
     * Get lab by its identifier.
     *
     * @param int $labId
     *
     * @return Lab
     */
    public function getLabById(int $labId): Lab
    {
        return $this->labRepository->getLabById($labId);
    }

    /**
     * Function to return time shift array for registrations in labQueueStatus
     *
     * @param $registrations
     * @param int $teachersNumber
     *
     * @return array
     */
    public function getEstimatedTimesToDefenceRegistrations($registrations, int $teachersNumber): array
    {
        $estDefTimes = [];
        $defLengths = [];

        //fill empty array for teachers
        $teachers = array_fill(0,$teachersNumber,0);

        //get list of defTimes
        foreach ($registrations as $key => $reg) {
            $defLengths[$key] = $reg->defense_duration;
        }

        //Fill the massive
        for($i = 0; $i < count($defLengths); $i++) {
            //find teacher what is loaded less than others.
            $teacherNr = array_keys($teachers, min($teachers))[0];
            //remember time on what this is possible teacherNr start current charon
            $estDefTimes[$i] = $teachers[$teacherNr];
            //add length of current charon teacherNr this teacher, simulating registered charon
            $teachers[$teacherNr] += $defLengths[$i];
        }
        return $estDefTimes;
    }

    /**
     * Function to return list of defence registrations for lab with:
     *  - number in queue
     *  - approximate start time
     *  - student name, if student name equals to username of requested student
     *
     * @param User $user
     * @param Lab $lab
     *
     * @return array
     */
    public function labQueueStatus(User $user, Lab $lab): array
    {
        $registrations = $this->defenseRegistrationRepository->getListOfLabRegistrationsByLabId($lab->id);

        $teachersNumber = $this->labTeacherRepository->countLabTeachers($lab->id);

        //Get lab start time and format date to timestamp
        $labStart = strtotime($lab->start);

        $defRegEstTimes = $this->getEstimatedTimesToDefenceRegistrations($registrations, $teachersNumber);

        foreach ($registrations as $key => $reg) {
            if($reg->student_id == $user->id) {
                $reg->student_name = $user->firstname . ' ' . $user->lastname;
            }
            else {
                $reg->student_name = "";
            }
            //show position in queue
            $reg->queue_pos = $key+1;

            //calculate estimated time
            $reg->approx_start_time = date("d.m.Y H:i", $labStart + $defRegEstTimes[$key] * 60);

            //delete not needed variables
            unset($reg->defense_duration);
            unset($reg->student_id);
        }

        return $registrations;
    }

    /**
     * Get ongoing and upcoming labs, including count of students registered
     * and estimated start time of next available defence.
     *
     * @param int $charonId
     *
     * @return array
     */
    public function findAvailableLabsByCharon(int $charonId): array
    {
        $labs = $this->labRepository->getAvailableLabsByCharonId($charonId);

        $charonLength = $this->charonRepository->getCharonById($charonId)->defense_duration;

        foreach ($labs as $lab) {

            $teacherNum = $this->labTeacherRepository->countLabTeachers($lab->id);
            $labStart = new Carbon(strtotime($lab->start));
            $capacity = (new Carbon(strtotime($lab->end)))->diff($labStart)->i;

            $defenceTimes = $this->defenseRegistrationRepository->getListOfLabRegistrationsByLabId($lab->id);
            $lab->defenders_num = count($defenceTimes);

            $queuePresumption = array_fill(0, $teacherNum, 0);

            foreach ($defenceTimes as $defenceTime) {
                $queuePresumption[array_keys($queuePresumption, min($queuePresumption))[0]] +=
                    $defenceTime->defense_duration;
            }

            $shortestWaitingTime = $queuePresumption[array_keys($queuePresumption, min($queuePresumption))[0]];

            if ($capacity >= $shortestWaitingTime + $charonLength) {
                $lab->estimated_start_time = $labStart->addMinutes($shortestWaitingTime);
            } else {
                $lab->estimated_start_time = null;
            }
        }

        return $labs;
    }
}
