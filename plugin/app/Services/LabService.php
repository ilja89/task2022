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
    ){
        $this->charonRepository = $charonRepository;
        $this->teacherRepository = $teacherRepository;
        $this->defenseRegistrationRepository = $defenseRegistrationRepository;
        $this->labRepository = $labRepository;
    }

    /** Function what will return list of labs related to charon with id of $charon, with following fields:
     *    - course_id: shows id of course what lab is related to
     *    - defenders_num: shows number of existing registrations for this lab
     *    - start: shows lab end time
     *    - end: shows lab end time
     *    - estimatedStartTime: shows estimated time when student registering on this lab will be defending
     *    - id: id of this lab
     *    - name: name of this lab
     * @param $charon
     * @return array
     */
    public function getLabsWithCapacityInfoForCharon($charon)
    {
        $labs = [];

        //Get length of this charon
        $thisCharonLength = $this->charonRepository->getCharonById($charon);
        $thisCharonLength = $thisCharonLength->defense_duration;

        //Get list of labs
        $allLabs = $this->labRepository->getLabsByCharonId($charon);

        //check if lab actual
        foreach ($allLabs as $lab) {
            if (strtotime($lab->end) > time()) {
                $labs[] = $lab;
            }
        }

        //Calculate lab capacity
        //Calculate avg defense length and check if lab can be booked
        foreach ($labs as $lab) {
            $lab->_thisCharonLength = $thisCharonLength; //DEBUG!
            $defTime = null;

            //Get teachers number
            $teacherNum = $this->teacherRepository->countLabTeachers($lab->id);
            $lab->_teacherNum = $teacherNum; //DEBUG!

            //Calculate lab capacity
            $capacity = ((strtotime($lab->end) - strtotime($lab->start)) / 60) * $teacherNum;
            $lab->_capacity = $capacity; //DEBUG!

            //Get all defense durations
            $defTimes = $this->defenseRegistrationRepository->getDefenseRegistrationsDurationsListByLabId($lab->id);
            $lab->_defTimes = $defTimes; //DEBUG!
            $lab->defenders_num = count($defTimes);

            //Sum them up and divide to get avg
            foreach ($defTimes as $time) {
                $defTime += $time->defense_duration;
            }

            $lab->_defTime = $defTime; //DEBUG!
            $lab->_thisCharonLength = $thisCharonLength; //DEBUG!
            if ($capacity - $defTime > $thisCharonLength) {
                $move = ($defTime / $teacherNum) * 60;
                $lab->estimatedStartTime = date("Y-m-d H:i:s", strtotime("$lab->start") + $move);
            } else {
                $lab->estimatedStartTime = null;
            }
        }

        return $labs;
    }
}