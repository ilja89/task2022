<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\LabRepository;
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
     * @var LabRepository
     */
    private $labRepository;

    /**
     * LabService constructor.
     * @param DefenseRegistrationRepository $defenseRegistrationRepository
     * @param LabTeacherRepository $labTeacherRepository
     */
    public function __construct(
        DefenseRegistrationRepository $defenseRegistrationRepository,
        LabTeacherRepository $labTeacherRepository,
        LabRepository $labRepository
    ) {
        $this->defenseRegistrationRepository = $defenseRegistrationRepository;
        $this->labTeacherRepository = $labTeacherRepository;
        $this->labRepository = $labRepository;
    }

    /**
     * Function to return time shift array for registrations in labQueueStatus
     *
     * @param $registrations
     * @param int $teachersNumber
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
            unset($reg->charon_length);
            unset($reg->student_id);
        }

        $queueStatus = [];

        $queueStatus['registrations'] = $registrations;

        $teachersList = $this->labRepository->getTeachersAndDefendingCharon($lab->id);

        $teachers = array();

        foreach ($teachersList as $key => $teacher) {
            $teacherId = $teacher->id;
            if (array_key_exists($teacherId, $teachers)){
                $teachers[$teacherId]['charons'] .= ', ' . $teacher->charon;
            } else {
                $teachers[$teacherId] = [];
                $teachers[$teacherId]['teacher'] = $teacher->firstname . ' ' . $teacher->lastname;
                if ($teacher->charon){
                    $teachers[$teacherId]['charons'] = $teacher->charon;
                    $teachers[$teacherId]['availability'] = 'Defending';
                } else {
                    $teachers[$teacherId]['availability'] = 'Free';
                }
            }
        }

        $queueStatus['teachers'] = $teachers;

        return $queueStatus;
    }

    public function findUpcomingOrActiveLabsByCharon(int $charonId){
        $result = $this->labRepository->getLabsWithStartAndEndTimes($charonId);
        foreach ($result as $lab){ //Getting all students-defenders who registered on defense lab
            $lab->defenders_num = $this->defenseRegistrationRepository->countDefendersByLab($lab->id);
        }
        return $result;
    }
}
