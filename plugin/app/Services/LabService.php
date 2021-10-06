<?php

namespace TTU\Charon\Services;

use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Repositories\LabTeacherRepository;
use Zeizig\Moodle\Models\User;

class LabService
{
    /**
     * @var LabRepository
     */
    private $labRepository;

    /**
     * @var LabTeacherRepository
     */
    private $labTeacherRepository;

    /**
     * LabService constructor.
     * @param LabRepository $labRepository
     * @param LabTeacherRepository $labTeacherRepository
     */
    public function __construct(
        LabRepository $labRepository,
        LabTeacherRepository $labTeacherRepository
    ) {
        $this->labRepository = $labRepository;
        $this->labTeacherRepository = $labTeacherRepository;
    }

    /**
     * Function to return time shift array for registrations in labQueueStatus
     *
     * @param $registrations
     * @param int $teachersNum
     * @return array
     */
    public function getApproximateTimeMoveForStudent($registrations, int $teachersNum): array
    {
        $defMoves = [];
        $defLengths = [];

        //fill empty array for teachers
        $teachers = array_fill(0,$teachersNum,0);

        //get list of defTimes
        foreach ($registrations as $key => $reg) {
            $defLengths[$key] = $reg->charon_length;
        }

        //Fill the massive
        for($i = 0; $i < count($defLengths); $i++) {
            //find teacher what is loaded less than others. $to is number of this teacher
            $to = array_keys($teachers, min($teachers))[0];
            //remember time on what this is possible to start current charon
            $defMoves[$i] = $teachers[$to];
            //add length of current charon to this teacher, simulating registered charon
            $teachers[$to] += $defLengths[$i];
        }
        return $defMoves;
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
        //get list of registrations
        $registrations = $this->labRepository->getListOfLabRegistrationsByLabIdReduced($lab->id);

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
        $move = $this->getApproximateTimeMoveForStudent($registrations, $teachers_num);

        //Calculate approximate time and delete not needed variables
        foreach ($registrations as $key => $reg) {
            $reg->approx_start_time = date("d.m.Y H:i", $labStart + $move[$key] * 60);
            unset($reg->charon_length);
            unset($reg->student_id);
        }

        return $registrations;
    }
}
