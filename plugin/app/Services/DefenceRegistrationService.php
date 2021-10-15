<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Exceptions\RegistrationException;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\CharonDefenseLabRepository;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\LabTeacherRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Repositories\UserRepository;
use Zeizig\Moodle\Globals\User as MoodleUser;

class DefenceRegistrationService
{
    /**
     * @see https://stackoverflow.com/questions/27878719/laravel-catch-eloquent-unique-field-error#answer-27879329
     */
    const DUPLICATE_ERROR_CODE = 1062;

    /** @var CharonRepository */
    private $charonRepository;

    /** @var LabTeacherRepository */
    private $teacherRepository;

    /** @var DefenseRegistrationRepository */
    private $defenseRegistrationRepository;

    /** @var MoodleUser */
    private $loggedInUser;

    /** @var UserRepository */
    private $userRepository;

    /** @var CharonDefenseLabRepository */
    private $defenseLabRepository;

    /** @var CharonService */
    private $charonService;

    /** @var SubmissionsRepository */
    private $submissionRepository;

    /**
     * @param CharonRepository $charonRepository
     * @param LabTeacherRepository $teacherRepository
     * @param DefenseRegistrationRepository $defenseRegistrationRepository
     * @param MoodleUser $loggedInUser
     * @param UserRepository $userRepository
     * @param CharonDefenseLabRepository $defenseLabRepository
     * @param CharonService $charonService
     * @param SubmissionsRepository $submissionRepository
     */
    public function __construct(
        CharonRepository $charonRepository,
        LabTeacherRepository $teacherRepository,
        DefenseRegistrationRepository $defenseRegistrationRepository,
        MoodleUser $loggedInUser,
        UserRepository $userRepository,
        CharonDefenseLabRepository $defenseLabRepository,
        CharonService $charonService,
        SubmissionsRepository $submissionRepository
    ) {
        $this->charonRepository = $charonRepository;
        $this->teacherRepository = $teacherRepository;
        $this->defenseRegistrationRepository = $defenseRegistrationRepository;
        $this->loggedInUser = $loggedInUser;
        $this->userRepository = $userRepository;
        $this->defenseLabRepository = $defenseLabRepository;
        $this->charonService = $charonService;
        $this->submissionRepository = $submissionRepository;
    }

    /**
     * Student registers for a defence
     *
     * @param int $studentId
     * @param int $submissionId
     * @param int $charonId
     * @param int $defenseLabId
     * @param ?string $progress
     */
    public function registerDefenceTime(
        int $studentId,
        int $submissionId,
        int $charonId,
        int $defenseLabId,
        ?string $progress = null
    ) {
        $user = $this->userRepository->find($studentId);

        $this->defenseRegistrationRepository->create([
            'student_name' => $user->firstname . ' ' . $user->lastname,
            'submission_id' => $submissionId,
            'student_id' => $studentId,
            'defense_lab_id' => $defenseLabId,
            'progress' => $progress != null ? $progress : "Waiting",
            'charon_id' => $charonId,
        ]);

        Log::info(json_encode([
            'event' => 'registration_creation',
            'by_user_id' => $this->loggedInUser->currentUserId(),
            'for_user_id' => $studentId,
            'defense_lab_id' => $defenseLabId,
            'submission_id' => $submissionId,
            'progress' => $progress,
        ]));
    }

    /**
     * @param string $date
     * @param int $charonId
     * @param Lab $lab
     * @param int $studentId
     * @param bool $ownTeacher
     *
     * @return array
     * @throws RegistrationException
     */
    public function getUsedDefenceTimes(string $date, int $charonId, Lab $lab, int $studentId, bool $ownTeacher): array
    {
        $charon = $this->charonRepository->getCharonById($charonId);
        $duration = $charon->defense_duration;
        $time = $lab->start->copy();
        $labTimeslots = [];
        $teacherCount = 1;

        while ($time->isBefore($lab->end)) {
            $labTimeslots[$time->format('H:i')] = $teacherCount;
            $time = $time->copy()->addMinutes($duration);
        }

        if ($ownTeacher) {
            $courseId = $charon->course;
            $teacher = $this->teacherRepository->getTeacherForStudent($studentId, $courseId);
            if ($teacher == null) {
                throw new RegistrationException('invalid_setup');
            }
            $labs = $this->defenseRegistrationRepository->getChosenTimesForTeacherAt($teacher->id, $date);
        } else {
            $teacherCount = $this->teacherRepository->countLabTeachers($lab->id);
            if ($teacherCount == 0) {
                throw new RegistrationException('invalid_setup');
            }
            $labs = $this->defenseRegistrationRepository->getChosenTimesForLabTeachers($date, $lab->id);
        }

        foreach ($labs as $taken) {
            $time = Carbon::parse($taken->choosen_time);

            if ($duration == $taken->defense_duration) {
                if (isset($labTimeslots[$time->format('H:i')])) {
                    $labTimeslots[$time->format('H:i')]--;
                }
            } else if ($duration < $taken->defense_duration) {
                $end = $time->copy()->addMinutes($taken->defense_duration);
                while ($time->isBefore($end)) {
                    if (isset($labTimeslots[$time->format('H:i')])) {
                        $labTimeslots[$time->format('H:i')]--;
                    }
                    $time->addMinutes($duration);
                }
            } else {
                $diff = $time->diffInMinutes($lab->start);

                $busy = $lab->start->copy()->addMinutes(intdiv($diff, $duration) * $duration);
                if (isset($labTimeslots[$busy->format('H:i')])) {
                    $labTimeslots[$busy->format('H:i')]--;
                }

                $end = $time->addMinutes($taken->defense_duration);
                if ($busy->addMinutes($duration)->isBefore($end)) {
                    if (isset($labTimeslots[$busy->format('H:i')])) {
                        $labTimeslots[$busy->format('H:i')]--;
                    }
                }
            }
        }

        return array_keys(array_filter($labTimeslots, function($teachersRemaining) {
            return $teachersRemaining < 1;
        }));
    }

    /**
     * Throw if lab has not enough capacity left for charon registration
     *
     * @param int $studentId
     * @param int $charonId
     * @param Lab $lab
     *
     * @throws RegistrationException
     */
    public function validateRegistration(int $studentId, int $charonId, Lab $lab)
    {
        $charonDuration = $this->charonRepository
            ->getCharonById($charonId)
            ->defense_duration;

        if ($charonDuration == null || $charonDuration <= 0) {
            throw new RegistrationException('invalid_setup');
        }

        $registrations = $this->defenseRegistrationRepository->getDefenseRegistrationDurationsByLab($lab->id);
        $totalOfDefenses = 0;
        $labDurationInterval = $lab->start->diff($lab->end);
        $labDuration = $labDurationInterval->h * 60 + $labDurationInterval->i;

        $teacherCount = $this->teacherRepository->countLabTeachers($lab->id);
        foreach ($registrations as $registration) {
            $totalOfDefenses += $registration->defense_duration;
        }

        if ($labDuration * $teacherCount < $totalOfDefenses + $charonDuration) {
            throw new RegistrationException("queue_full");
        }

        $pendingStudentDefences = $this->defenseRegistrationRepository->getUserPendingRegistrationsCount(
            $studentId,
            $charonId,
            $lab->id
        );

        if ($pendingStudentDefences > 0) {
            throw new RegistrationException('user_in_db');
        }
    }

    /**
     * @param int $studentId
     * @param int $charonId
     * @param string $chosenTime
     * @param Lab $lab
     *
     * @throws RegistrationException
     */
    public function validateDefence(int $studentId, int $charonId, string $chosenTime, Lab $lab)
    {
        $duration = $this->charonRepository
            ->getCharonById($charonId)
            ->defense_duration;

        if ($duration == null || $duration <= 0) {
            throw new RegistrationException('invalid_setup');
        }

        $delta = Carbon::createFromFormat('Y-m-d H:i', $chosenTime)->diffInSeconds($lab->start) / 60;
        if ($delta % $duration != 0) {
            throw new RegistrationException('invalid_chosen_time');
        }

        $pendingStudentDefences = $this->defenseRegistrationRepository->getUserPendingRegistrationsCount(
            $studentId,
            $charonId,
            $lab->id
        );

        if ($pendingStudentDefences > 0) {
            throw new RegistrationException('user_in_db');
        }
    }

    /**
     * Finds a random teacher if ownTeacher is not required. Otherwise finds the first ownTeacher and checks if said
     * teacher is available. If a student has multiple ownTeacher-s then this method won't find all available
     * teachers (e.g. first busy, second free).
     *
     * @param int $studentId
     * @param bool $ownTeacher
     * @param int $labId
     * @param int $charonId
     * @param Carbon $chosenTime
     *
     * @return int
     * @throws RegistrationException
     */
    public function getTeacherId(
        int $studentId,
        bool $ownTeacher,
        int $labId,
        int $charonId,
        Carbon $chosenTime
    ): int {
        if (!$ownTeacher) {
            return $this->getTeachersByCharonAndLab($charonId, $labId, $chosenTime);
        }

        $courseId = $this->charonRepository->getCharonById($charonId)->course;
        $teacherId = $this->teacherRepository->getTeacherForStudent($studentId, $courseId)->id;
        $busy = $this->defenseRegistrationRepository->isTeacherBusyAt($teacherId, $chosenTime);

        if ($busy) {
            throw new RegistrationException('teacher_is_busy');
        }

        return $teacherId;
    }

    /**
     * @param $charonId
     * @param $labId
     * @param Carbon $studentTime
     *
     * @return int
     * @throws RegistrationException
     */
    private function getTeachersByCharonAndLab($charonId, $labId, Carbon $studentTime): int
    {
        $labTeachers = $this->teacherRepository->getTeachersByCharonAndLab($charonId, $labId);
        if ($labTeachers->isEmpty()) {
            throw new RegistrationException('no_teacher_available');
        }

        $teacherIds = $labTeachers->pluck('id')->all();

        $busyTeachers = $this->teacherRepository->checkWhichTeachersBusyAt($teacherIds, $studentTime);

        $availableTeachers = array_diff($teacherIds, $busyTeachers);
        if (empty($availableTeachers)) {
            throw new RegistrationException('no_teacher_available');
        }

        return $availableTeachers[array_rand($availableTeachers)];
    }

    /**
     * Registers a student for a defence. If submission identifier is null then try to find it.
     *
     * Throws if:
     *  given charon setup is invalid;
     *  user already has a defence registered for given charon in given lab;
     *  not enough time left for given charon;
     *  student does not have an ungraded submission in given Charon.
     *
     * @param int $studentId
     * @param int $charonId
     * @param int $defenseLabId
     * @param ?int $submissionId
     * @param ?string $progress
     *
     * @return string
     * @throws RegistrationException
     */
    public function registerDefence(
        int $studentId,
        int $charonId,
        int $defenseLabId,
        ?int $submissionId,
        ?string $progress = null
    ): string {

        $lab = $this->defenseLabRepository->getLabByDefenseLabId($defenseLabId);

        if ($submissionId === null) {

            $submission = $this->submissionRepository->getLatestUngradedSubmission(
                $this->charonService->getCharonById($charonId)->id,
                $studentId
            );

            if ($submission === null) {
                throw new RegistrationException("no_submission");
            }

            $submissionId = $submission->id;
        }

        $this->validateRegistration($studentId, $charonId, $lab);

        $this->registerDefenceTime(
            $studentId,
            $submissionId,
            $charonId,
            $defenseLabId,
            $progress
        );

        return 'inserted';
    }
}
