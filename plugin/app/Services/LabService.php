<?php

namespace TTU\Charon\Services;

class LabService
{
    /** Function to return time shift array for registrations in labQueueStatus
     *  return list of time shifts
     * //gives approximate time move since lab start for each student based on their charon lengths and teacher number
     * // SERVICE PART OF "LabRepository->labQueueStatus() function"
     * @param Object $registrations
     * @param int $teachersNum
     * @return Array
     */
    public function getApproximateTimeMoveForStudent(Object $registrations, int $teachersNum): Array
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
}