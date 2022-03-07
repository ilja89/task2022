<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Repositories\LabTeacherRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
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

    /** @var SubmissionsRepository */
    private $submissionsRepository;

    /**
     * LabService constructor.
     *
     * @param DefenseRegistrationRepository $defenseRegistrationRepository
     * @param LabTeacherRepository $labTeacherRepository
     * @param LabRepository $labRepository,
     * @param CharonRepository $charonRepository
     * @param DefenceRegistrationService $defenceRegistrationService
     * @param SubmissionsRepository $submissionsRepository
     */
    public function __construct(
        DefenseRegistrationRepository $defenseRegistrationRepository,
        LabTeacherRepository $labTeacherRepository,
        LabRepository $labRepository,
        CharonRepository $charonRepository,
        DefenceRegistrationService $defenceRegistrationService,
        SubmissionsRepository $submissionsRepository
    ) {
        $this->defenseRegistrationRepository = $defenseRegistrationRepository;
        $this->labTeacherRepository = $labTeacherRepository;
        $this->labRepository = $labRepository;
        $this->charonRepository = $charonRepository;
        $this->defenceRegistrationService = $defenceRegistrationService;
        $this->submissionsRepository = $submissionsRepository;
    }

    /**
     * Update lab.
     *
     * @param $labId
     * @param $startDateTime
     * @param $endDateTime
     * @param $labName
     * @param $teachers
     * @param $charons
     * @param $groups
     * @param $groupings
     * @param $type
     * @return Lab
     */
    public function update($labId, $startDateTime, $endDateTime, $labName, $teachers, $charons, $groups, $groupings, $type): Lab
    {
        $removedTeachers = $this->labTeacherRepository->getTeachersByLabWhichNotInList($labId, $teachers);
        $updatedLab = $this->labRepository->update(
            $labId,
            $startDateTime,
            $endDateTime,
            $labName,
            $teachers,
            $charons,
            $groups,
            $groupings,
            $type
        );
        if (count($removedTeachers) > 0) {
            $this->defenseRegistrationRepository
                ->removeTeachersFromUndoneRegistrations($labId, $removedTeachers);
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
        $queueStatus = [];

        // Send lab start and end times.
        // This is used if teacher changes lab time and to show correct tables to students in queue
        $queueStatus['lab_start'] = $lab->start;
        $queueStatus['lab_end'] = $lab->end;

        if (Carbon::now() > $lab->end){
            return $queueStatus;
        }

        // Get teachers per lab
        $teachersList = $this->labTeacherRepository->getAllLabTeachersByLab($lab->id);

        $teachersCount = count($teachersList);

        // Get defending charon per teacher
        $teachersDefences = $this->defenseRegistrationRepository->getTeacherAndDefendingCharonByLab($lab->id);

        // Get list of registrations. If lab started, then only waiting status
        // registrations and add teachers and defending charons per teacher
        if (Carbon::now() >= $lab->start){

            foreach ($teachersList as $key => $teacher) {

                $teacher->teacher_name = $teacher->firstname . ' ' . $teacher->lastname;
                $teacher->charon = '';

                // Check if teacher is defending some charon or not
                foreach ($teachersDefences as $teachersDefence) {
                    if ($teachersDefence->teacher_id === $teacher->id){
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

            $labRegistrations = $this->defenseRegistrationRepository
                ->getLabRegistrationsByLabId($lab->id, ['Waiting']);
        } else {
            $labRegistrations = $this->defenseRegistrationRepository
                ->getLabRegistrationsByLabId($lab->id, ['Waiting', 'Defending']);
        }

        $registrations = $this->defenceRegistrationService->attachEstimatedTimesToDefenceRegistrations(
            $labRegistrations,
            $teachersCount,
            $lab->start
        );

        for ($i = 0; $i < count($registrations); $i++) {

            $registration = $registrations[$i];

            if ($registration->type == 'Teams') {
                $submissionUsers = $this->submissionsRepository->getSubmissionUsers($registration->submission_id);
                $groupStudentNames = '';
                foreach ($submissionUsers as $submissionUser) {
                    $groupStudentNames .= ', ' . $submissionUser->firstname . ' ' .  $submissionUser->lastname;
                }
                $registration->student_name = substr($groupStudentNames, 1);
            } elseif ($registration->student_id == $user->id) {
                $registration->student_name = $user->firstname . " " . $user->lastname;
            } else {
                $registration->student_name = "";
            }

            $registration->queue_pos = $i + 1;
            $registration->estimated_start = date("d.m.Y H:i", $registration->estimated_start->timestamp);

            unset($registration->defense_duration);
            unset($registration->student_id);
        }

        $queueStatus['registrations'] = $registrations;

        return $queueStatus;
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
        $labs = $this->labRepository->getAvailableLabsByCharonIdCheckGroupMembership($charonId, app(\Zeizig\Moodle\Globals\User::class)->currentUserId());
        $charon = $this->charonRepository->getCharonById($charonId);

        foreach ($labs as $lab) {
            $lab->new_defence_start = $this->defenceRegistrationService
                ->getEstimateTimeForNewRegistration($lab, $charon);
            $lab->defenders_num = $this->defenseRegistrationRepository->countUndoneDefendersByLab($lab->id);
        }

        return $labs;
    }
}
