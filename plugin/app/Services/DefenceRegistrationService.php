<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Exceptions\RegistrationException;
use TTU\Charon\Models\Charon;
use TTU\Charon\Models\Lab;
use TTU\Charon\Models\Registration;
use TTU\Charon\Repositories\CharonDefenseLabRepository;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\LabTeacherRepository;
use TTU\Charon\Repositories\SubmissionsRepository;
use TTU\Charon\Repositories\UserRepository;
use Zeizig\Moodle\Globals\User;
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
     * DefenceRegistrationService constructor.
     *
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
            'defense_start' => $progress == 'Defending' ? Carbon::now() : null
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
        $charon = $this->charonRepository->getCharonById($charonId);

        if ($charon->defense_duration == null || $charon->defense_duration <= 0) {
            throw new RegistrationException('invalid_setup');
        }

        $pendingStudentDefences = $this->defenseRegistrationRepository->getUserPendingRegistrationsCount(
            $studentId,
            $charonId,
            $lab->id
        );

        if ($pendingStudentDefences > 0) {
            throw new RegistrationException('user_in_db');
        }

        if ($this->getEstimateTimeForNewRegistration($lab, $charon) === null) {
            throw new RegistrationException("not_enough_time");
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
        string $progress = 'Waiting'
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

    /**
     * Give registrations their approximate starting time and sort them by it.
     *
     * @param array $registrations
     * @param int $teacherCount
     * @param Carbon $labStart
     * @param $teachersDefences
     *
     * @return array
     */
    public function attachEstimatedTimesToDefenceRegistrations(
        array $registrations,
        int $teacherCount,
        Carbon $labStart,
        $teachersDefences
    ): array {

        //If lab started, then queue starts from now
        if (Carbon::now() >= $labStart) {
            $queueStart = Carbon::now();
        } else {
            $queueStart = $labStart;
        }

        if ($teacherCount < 1) {
            $teacherCount = 1;
        }
        $queuePresumption = array_fill(0, $teacherCount, 0);

        foreach ($teachersDefences as $teachersDefence) {
            $defenseStart = Carbon::parse($teachersDefence->defense_start);
            $teacherNr = array_keys($queuePresumption, min($queuePresumption))[0];

            $defenceAndQueueStartDifference = $defenseStart->copy()->diffInMinutes($queueStart);

            if ($defenceAndQueueStartDifference < $teachersDefence->defense_duration) {
                $queuePresumption[$teacherNr] += $teachersDefence->defense_duration - $defenceAndQueueStartDifference;
            }
        }

        $queueRegistrations = [];


        for ($i = 0; $i < count($registrations); $i++) {
            $teacherNr = array_keys($queuePresumption, min($queuePresumption))[0];

            $registration = $registrations[$i];

            $registration->estimated_start =
                $queueStart->copy()->addMinutes($queuePresumption[$teacherNr]);
            $timeLeft = $registration->estimated_start->diff(Carbon::now());
            if ($timeLeft->h > 0) {
                $registration->time_left = '>60 min';
            } else {
                $registration->time_left = $timeLeft->format('%i min');
            }

            $queuePresumption[$teacherNr] += $registration->defense_duration;

            unset($registration->defense_start);
            unset($registration->progress);
            array_push($queueRegistrations, $registration);
        }

        return $queueRegistrations;
    }

    /**
     * Calculate estimated starting time for a new defence registration.
     *
     * @param Lab $lab
     * @param Charon $charon
     *
     * @return Carbon|null
     */
    public function getEstimateTimeForNewRegistration(Lab $lab, Charon $charon): ?Carbon
    {
        $capacity = $lab->end->diffInMinutes($lab->start);
        $teacherCount = $this->teacherRepository->countLabTeachers($lab->id);

        if ($teacherCount === 0) {
            // if lab has no teachers, then block registrations for this lab
            return null;
        }

        $registrations = $this->attachEstimatedTimesToDefenceRegistrations(
            $this->defenseRegistrationRepository->getLabRegistrationsByLabId($lab->id, ['Waiting', 'Defending']),
            $teacherCount,
            $lab->start,
            $this->defenseRegistrationRepository->getTeacherAndDefendingCharonByLab($lab->id)
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

        return $capacity >= $shortestWaitingTime->diffInMinutes($lab->start) + $charon->defense_duration
            ? $shortestWaitingTime
            : null;
    }

    /**
     * Get defense registrations by course. The needed is only $courseId, other parameters are used only for filtering.
     *
     * @param $courseId
     * @param $after - is used to get registrations where lab ends after this time
     * @param $before - is used to get registrations where lab starts before this time
     * @param $teacher_id
     * @param $progress - status of the registration - 'Waiting', 'Defending' or 'Done'
     * @param bool $sessionStarted - is used to filter out others teachers' registrations to get only free
     * registrations and registration by $teacherId, if $sessionStarted parameter is true.
     * @return Registration[]
     */
    public function getDefenseRegistrationsByCourseFiltered($courseId, $after, $before, $teacher_id, $progress, bool $sessionStarted)
    {
        $defenseRegistrations = $this->defenseRegistrationRepository
            ->getDefenseRegistrationsByCourseFiltered($courseId, $after, $before, $teacher_id, $progress, $sessionStarted);
        $labId = null;
        $labTeachers = [];
        foreach ($defenseRegistrations as $defenseRegistration) {
            if ($labId === null || $labId !== $defenseRegistration->lab_id){
                $labId = $defenseRegistration->lab_id;
                $labTeachers = $this->teacherRepository->getTeachersByCharonAndLab($defenseRegistration->charon_id, $labId);
            }
            $defenseLabTeachers = $labTeachers->toArray();
            if ($defenseRegistration->teacher['id']) {
                $teacherExists = false;
                foreach ($defenseLabTeachers as $defenseLabTeacher) {
                    if ($defenseLabTeacher->id == $defenseRegistration->teacher['id']) {
                        $teacherExists = true;
                    }
                }
                if (!$teacherExists) {
                    array_push($defenseLabTeachers, $defenseRegistration->teacher);
                }
            }
            $defenseRegistration->lab_teachers = $defenseLabTeachers;
        }
        return $defenseRegistrations;
    }

    /**
     * Update registration progress or/and teacher.
     * Check if current user is lab teacher of lab, in which registration belongs,
     * so has rights to manage this registration.
     * If no teacher given and progress 'Defending' or 'Done', then marking currently logged user as teacher.
     *
     * @param $defenseId
     * @param $newProgress
     * @param $newTeacherId
     * @return Registration
     * @throws RegistrationException
     */
    public function updateRegistration($defenseId, $newProgress, $newTeacherId): Registration
    {
        $userId = app(User::class)->currentUserId();

        $labTeacher = $this->teacherRepository
            ->getTeacherByDefenseAndUserId($defenseId, $userId);
        if ($labTeacher == null) {
            throw new RegistrationException("invalid_lab_teacher");
        } else if ($newTeacherId == null && $newProgress !== 'Waiting') {
            $newTeacherId = $userId;
            $updatedDefenseTeacher = $labTeacher;
        } else if ($newTeacherId == null) {
            $updatedDefenseTeacher = null;
        } else {
            $updatedDefenseTeacher = $this->teacherRepository->getTeacherByUserId($newTeacherId);
        }

        $defenseStart = null;
        if ($newProgress == 'Defending') {
            $defenseStart = Carbon::now();
        }
        $defense = $this->defenseRegistrationRepository
            ->updateRegistration($defenseId, $newProgress, $newTeacherId, $defenseStart);
        $defense->teacher = $updatedDefenseTeacher;
        $labTeachers = $this->teacherRepository->getTeachersByCharonAndDefenseLab($defense->charon_id, $defense->defense_lab_id);

        if ($defense->teacher && $defense->teacher->id && !$labTeachers->contains($defense->teacher)) {
            $labTeachers->push($defense->teacher);
        }
        $defense->lab_teachers = $labTeachers;

        return $defense;
    }

    /**
     * Deletes registration, also checks that teacher has rights on this.
     *
     * @param $studentId
     * @param $defenseLabId
     * @param $submissionId
     * @param $userId
     * @return int
     * @throws RegistrationException
     */
    public function delete($studentId, $defenseLabId, $submissionId)
    {
        // Check if current user is lab teacher of lab, in which registration belongs
        $labTeacher = $this->teacherRepository
            ->getTeacherByDefenseLabAndUserId($defenseLabId, app(User::class)->currentUserId());
        if ($labTeacher == null) {
            throw new RegistrationException("invalid_lab_teacher");
        }

        Log::warning(json_encode([
            'event' => 'registration_deletion',
            'by_user_id' => app(User::class)->currentUserId(),
            'for_user_id' => $studentId,
            'defense_lab_id' => $defenseLabId,
            'submission_id' => $submissionId
        ]));
        return $this->defenseRegistrationRepository->deleteRegistration($studentId, $defenseLabId, $submissionId);

    }
}
