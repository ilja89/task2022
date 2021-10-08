<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\LabTeacherRepository;
use Zeizig\Moodle\Models\User;

class LabService
{
    /**
     * @var DefenseRegistrationRepository
     */
    private $defenseRegistrationRepository;

    /**
     * @var LabTeacherRepository
     */
    private $labTeacherRepository;

    /**
     * LabService constructor.
     * @param DefenseRegistrationRepository $defenseRegistrationRepository
     * @param LabTeacherRepository $labTeacherRepository
     */
    public function __construct(
        DefenseRegistrationRepository $defenseRegistrationRepository,
        LabTeacherRepository $labTeacherRepository
    ) {
        $this->defenseRegistrationRepository = $defenseRegistrationRepository;
        $this->labTeacherRepository = $labTeacherRepository;
    }

    /**
     * Function to return time shift array for registrations in labQueueStatus
     *
     * @param $registrations
     * @param int $teachersNum
     * @return array
     */
    public function getEstimatedTimesToDefenceRegistrations($registrations, int $teachersNum): array
    {
        $estDefTimes = [];
        $defLengths = [];

        //fill empty array for teachers
        $teachers = array_fill(0,$teachersNum,0);

        //get list of defTimes
        foreach ($registrations as $key => $reg) {
            $defLengths[$key] = $reg->charon_length;
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
     *  Function to return list of defence registrations for lab with:
     *  - number in queue
     *  - approximate start time
     *  - student name, if student name equals to username of requested student
     *
     * @param User $user
     * @param Lab $lab
     * @return array
     */
    public function labQueueStatus(User $user, Lab $lab): array
    {
        //get list of registrations. If lab started, then only waiting
        if (Carbon::now() >= $lab->start){
            $registrations = $this->defenseRegistrationRepository->getListOfLabRegistrationsWithWaitingStatsIfLabStartedReduced($lab->id);
        } else {
            $registrations = $this->defenseRegistrationRepository->getListOfLabRegistrationsIfLabNotStartedReduced($lab->id);
        }

        //get number of teachers assigned to lab
        $teachers_num = $this->labTeacherRepository->countLabTeachers($lab->id);

        //Get lab start time and format date to timestamp
        $labStart = strtotime($lab->start);

        foreach ($registrations as $key => $reg) {
            if($reg->student_id == $user->id) {
                $reg->student_name = $user->firstname . ' ' . $user->lastname;
            }
            else {
                $reg->student_name = "";
            }
            //show position in queue
            $reg->queue_pos = $key+1;

        }
        $defRegEstTimes = $this->getEstimatedTimesToDefenceRegistrations($registrations, $teachers_num);

        //Calculate approximate time and delete not needed variables
        foreach ($registrations as $key => $reg) {
            $reg->approx_start_time = date("d.m.Y H:i", $labStart + $defRegEstTimes[$key] * 60);
            unset($reg->charon_length);
            unset($reg->student_id);
        }

        $queueStatus = [];

        $queueStatus['registrations'] = $registrations;

        return $queueStatus;
    }
}
