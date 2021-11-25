<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Repositories\LabTeacherRepository;
use Zeizig\Moodle\Models\Course;
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

    /** @var DefenceRegistrationService */
    private $defenceRegistrationService;

    /**
     * LabService constructor.
     *
     * @param DefenseRegistrationRepository $defenseRegistrationRepository
     * @param LabTeacherRepository $labTeacherRepository
     * @param LabRepository $labRepository,
     * @param CharonRepository $charonRepository
     * @param DefenceRegistrationService $defenceRegistrationService
     */
    public function __construct(
        DefenseRegistrationRepository $defenseRegistrationRepository,
        LabTeacherRepository $labTeacherRepository,
        LabRepository $labRepository,
        CharonRepository $charonRepository,
        DefenceRegistrationService $defenceRegistrationService
    ) {
        $this->defenseRegistrationRepository = $defenseRegistrationRepository;
        $this->labTeacherRepository = $labTeacherRepository;
        $this->labRepository = $labRepository;
        $this->charonRepository = $charonRepository;
        $this->defenceRegistrationService = $defenceRegistrationService;
    }

    /**
     * Update lab.
     *
     * @param Request $request
     * @param Course $course
     * @param Lab $lab
     * @return Lab
     */
    public function update(Request $request, Course $course, Lab $lab): Lab
    {
        $removedTeachers = $this->labTeacherRepository->getTeachersByLabWhichNotInList($lab->id, $request['teachers']);
        $updatedLab = $this->labRepository->update(
            $lab->id,
            $request['start'],
            $request['end'],
            $request['name'],
            $request['teachers'],
            $request['charons'],
            $request['groups']
        );
        if (count($removedTeachers) > 0){
            $this->defenseRegistrationRepository->removeTeachersFromWaitingAndDefendingRegistrations($lab->id, $removedTeachers);
        }
        return $updatedLab;
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
        $registrations = $this->defenceRegistrationService->attachEstimatedTimesToDefenceRegistrations(
            $this->defenseRegistrationRepository->getListOfUndoneLabRegistrationsByLabId($lab->id),
            $this->labTeacherRepository->countLabTeachers($lab->id),
            Carbon::parse($lab->start)
        );

        for ($i = 0; $i < count($registrations); $i++) {

            if ($registrations[$i]->student_id == $user->id) {
                $registrations[$i]->student_name = $user->firstname . " " . $user->lastname;
            } else {
                $registrations[$i]->student_name = "";
            }

            $registrations[$i]->queue_pos = $i + 1;
            $registrations[$i]->estimated_start = date("d.m.Y H:i", $registrations[$i]->estimated_start->timestamp);

            unset($registrations[$i]->defense_duration);
            unset($registrations[$i]->student_id);
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
        $charon = $this->charonRepository->getCharonById($charonId);

        foreach ($labs as $lab) {
            $lab->new_defence_start = $this->defenceRegistrationService
                ->getEstimateTimeForNewRegistration($lab, $charon);
            $lab->defenders_num = $this->defenseRegistrationRepository->countUndoneDefendersByLab($lab->id);
        }

        return $labs;
    }
}
