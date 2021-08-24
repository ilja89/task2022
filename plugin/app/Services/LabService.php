<?php

namespace TTU\Charon\Services;

use stdClass;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Repositories\LabTeacherRepository;
use TTU\Charon\Repositories\UserRepository;
use TTU\Charon\Services\TimeService;

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
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var \TTU\Charon\Services\TimeService
     */
    private $timeService;


    /**
     * LabService constructor.
     * @param LabRepository $labRepository
     * @param LabTeacherRepository $labTeacherRepository
     * @param UserRepository $userRepository
     * @param \TTU\Charon\Services\TimeService $timeService
     */
    public function __construct(
        LabRepository $labRepository,
        LabTeacherRepository $labTeacherRepository,
        UserRepository $userRepository,
        TimeService $timeService
    ){
        $this->labRepository = $labRepository;
        $this->labTeacherRepository = $labTeacherRepository;
        $this->userRepository = $userRepository;
        $this->timeService = $timeService;
    }

    /** Function to return time shift array for registrations in labQueueStatus
     *  return list of time shifts
     *  gives approximate time move since lab start for each student based on their charon lengths and teacher number
     *  Part of labQueueStatus() function
     * @param stdClass $registrations
     * @param int $teachersNum
     * @return array
     */
    public function getApproximateTimeMoveForStudent(stdClass $registrations, int $teachersNum): array
    {
        $defMoves = [];
        $defLengths = [];

        //fill empty array for teachers
        $teachers = array_fill(0,$teachersNum,0);

        //get list of defTimes
        foreach ($registrations as $key => $reg)
        {
            $defLengths[$key] = $reg->charon_length;
        }

        //Fill the massive
        for($i = 0; $i < count($defLengths); $i++)
        {
            //find teacher what is loaded less than others. $to is number of this teacher
            $to = array_keys($teachers, min($teachers))[0];
            //remember time on what this is possible to start current charon
            $defMoves[$i] = $teachers[$to];
            //add length of current charon to this teacher, simulating registered charon
            $teachers[$to] += $defLengths[$i];
        }
        $defMoves[] = $teachers; //DEBUG!
        return $defMoves;
    }

    /** Function to return list of defence registrations for lab with:
     *  - number in queue
     *  - charon id
     *  - approximate start time
     *  - student name, if student name equals to username of requested student
     * @param int $userId
     * @param int $labId
     * @return stdClass
     */
    public function labQueueStatus(int $userId, int $labId): stdClass
    {
        //get list of registrations
        $result = $this->labRepository->getListOfLabRegistrationsByLabIdReduced($labId);

        //get number of teachers assigned to lab
        $teachers_num = $this->labTeacherRepository->countLabTeachers($labId);

        //Get times when lab starts and ends
        $labTime = $this->labRepository->getLabStartEndTimesByLabId($labId);

        //Format date to timestamp
        $labTime = $this->timeService->formatDateObjectToTimestamp($labTime);

        foreach ($result as $key => $reg)
        {
            //if student id equals to user id, then return username as field, else set it null
            if($reg->student_id == $userId)
            {
                $reg->student_name = $this->userRepository->getUsernameById($userId);
            }
            else
            {
                $reg->student_name = "";
            }

            //show position in queue
            $reg->queue_pos = $key+1;

        }
        $move = $this->getApproximateTimeMoveForStudent($result, $teachers_num);

        //Calculate approximate time and delete not needed variables
        foreach ($result as $key => $reg)
        {
            $reg->approxStartTime = date("d.m.Y H:i", $labTime->start + $move[$key] * 60);
            unset($reg->charon_length);
            unset($reg->student_id);
        }
        $result['move'] = $move; //DEBUG!

        return $result;

    }
}
