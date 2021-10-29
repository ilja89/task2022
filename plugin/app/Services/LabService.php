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
     * Get ongoing and upcoming labs, including students registered for each lab
     * with given charon identifier got from request.
     *
     * @param int $charonId
     *
     * @return mixed
     */
    public function findLabsByCharonIdLaterEqualToday(int $charonId)
    {
        return $this->labRepository->getLabsByCharonIdLaterEqualToday($charonId);
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
        if (Carbon::now() > $lab->end){
            return [];
        }

        $queueStatus = [];

        // Get teachers per lab
        $teachersList = $this->labTeacherRepository->getAllLabTeachersByLab($lab->id);

        $teachersCount = count($teachersList);

        // Get list of registrations. If lab started, then only waiting status
        // registrations and add teachers and defending charons per teacher
        if (Carbon::now() >= $lab->start){
            $registrations = $this->defenseRegistrationRepository->getLabRegistrationsByLabId($lab->id, ['Waiting']);
            $queueFirstDefenseTime = strtotime(Carbon::now());

            // Get defending charon per teacher
            $teachersDefences = $this->defenseRegistrationRepository->getTeacherAndDefendingCharonByLab($lab->id);

            foreach ($teachersList as $key => $teacher) {

                $teacher->teacher_name = $teacher->firstname . ' ' . $teacher->lastname;
                $teacher->charon = '';

                // Check if teacher is defending some charon or not
                foreach ($teachersDefences as $teachersDefence) {
                    if ($teacher->charon === '' && $teachersDefence->teacher_id === $teacher->id){
                        $teacher->charon = $teachersDefence->charon;
                    }
                }

                // Add defending or not status
                if ($teacher->charon){
                    $teacher->availability = 'Defending';
                } else {
                    $teacher->availability = 'Free';
                }

                // Unset unuseful data
                unset($teacher->id);
                unset($teacher->firstname);
                unset($teacher->lastname);
            }

            $queueStatus['teachers'] = $teachersList;
        } else {
            $registrations = $this->defenseRegistrationRepository->getLabRegistrationsByLabId($lab->id);
            $queueFirstDefenseTime = strtotime($lab->start);
        }

        //Get lab start time and format date to timestamp
        $defenceTimes = $this->getEstimatedTimesToDefenceRegistrations($registrations, $teachersCount);

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
            $reg->approx_start_time = date("d.m.Y H:i", $queueFirstDefenseTime + $defenceTimes[$key] * 60);

            //delete not needed variables
            unset($reg->charon_length);
            unset($reg->student_id);
        }

        $queueStatus['registrations'] = $registrations;

        return $queueStatus;
    }

    public function findUpcomingOrActiveLabsByCharon(int $charonId){
        $result = $this->labRepository->getLabsWithStartAndEndTimes($charonId);
        foreach ($result as $lab){ //Getting all students-defenders who registered on defense lab
            $lab->defenders_num = $this->defenseRegistrationRepository->countDefendersByLab($lab->defense_lab_id);
        }
        return $result;
    }
}
