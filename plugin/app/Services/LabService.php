<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use TTU\Charon\Models\Charon;
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
        $registrations = $this->attachEstimatedTimesToDefenceRegistrations(
            $this->defenseRegistrationRepository->getListOfLabRegistrationsByLabId($lab->id),
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
            $lab->new_defence_start = $this->getEstimateTimeForNewRegistration($lab, $charon);
            $lab->defenders_num = $this->defenseRegistrationRepository->countDefendersByLab($lab->id);
        }

        return $labs;
    }

    /**
     * Give registrations their approximate starting time and sort them by it.
     *
     * @param array $registrations
     * @param int $teacherCount
     * @param Carbon $labStart
     *
     * @return array
     */
    private function attachEstimatedTimesToDefenceRegistrations(
        array $registrations,
        int $teacherCount,
        Carbon $labStart
    ): array {
        $queuePresumption = array_fill(0, $teacherCount, 0);

        for ($i = 0; $i < count($registrations); $i++) {
            $teacherNr = array_keys($queuePresumption, min($queuePresumption))[0];

            $registrations[$i]->estimated_start =
                $labStart->copy()->addMinutes($queuePresumption[$teacherNr]);

            $queuePresumption[$teacherNr] += $registrations[$i]->defense_duration;
        }

        usort(
            $registrations,
            function ($r1, $r2) {
                return $r1->estimated_start > $r2->estimated_start;
            }
        );

        return $registrations;
    }

    /**
     * Calculate estimated starting time for a new defence registration.
     *
     * @param Lab $lab
     * @param Charon $charon
     *
     * @return Carbon|null
     */
    private function getEstimateTimeForNewRegistration(Lab $lab, Charon $charon): ?Carbon
    {
        $capacity = $lab->end->diff($lab->start)->i;
        $teacherCount = $this->labTeacherRepository->countLabTeachers($lab->id);

        $registrations = $this->attachEstimatedTimesToDefenceRegistrations(
            $this->defenseRegistrationRepository->getListOfLabRegistrationsByLabId($lab->id),
            $teacherCount,
            $lab->start
        );

        if (count($registrations) >= $teacherCount) {

            $latestRegistrations = array_slice($registrations, count($registrations) - $teacherCount);

            $shortestWaitingTimeRegistration = array_reduce(
                $latestRegistrations,
                function ($r1, $r2) {
                    return $r1 !== null && $r1->estimated_start < $r2->estimated_start
                        ? $r1
                        : $r2;
                }
            );

            $shortestWaitingTime = $shortestWaitingTimeRegistration->estimated_start
                ->addMinutes($shortestWaitingTimeRegistration->defense_duration);

        } else {
            $shortestWaitingTime = $lab->start;
        }

        return $capacity >= $shortestWaitingTime->diff($lab->start)->i + $charon->defense_duration
            ? $shortestWaitingTime
            : null;
    }
}
