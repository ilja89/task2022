<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Exceptions\RegistrationException;
use TTU\Charon\Models\Lab;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Repositories\LabTeacherRepository;
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

    /** @var LabRepository */
    private $labRepository;

    /** @var DefenseRegistrationRepository */
    private $defenseRegistrationRepository;

    /** @var MoodleUser */
    private $loggedInUser;

    /** @var UserRepository */
    private $userRepository;

    /**
     * @param CharonRepository $charonRepository
     * @param LabTeacherRepository $teacherRepository
     * @param LabRepository $labRepository
     * @param DefenseRegistrationRepository $defenseRegistrationRepository
     * @param MoodleUser $loggedInUser
     * @param UserRepository $userRepository
     */
    public function __construct(
        CharonRepository $charonRepository,
        LabTeacherRepository $teacherRepository,
        LabRepository $labRepository,
        DefenseRegistrationRepository $defenseRegistrationRepository,
        MoodleUser $loggedInUser,
        UserRepository $userRepository
    ) {
        $this->charonRepository = $charonRepository;
        $this->teacherRepository = $teacherRepository;
        $this->labRepository = $labRepository;
        $this->defenseRegistrationRepository = $defenseRegistrationRepository;
        $this->loggedInUser = $loggedInUser;
        $this->userRepository = $userRepository;
    }

    /**
     * Student registers for a defence time slot
     *
     * @param int $studentId
     * @param int $submissionId
     * @param bool $ownTeacher
     * @param int $charonId
     * @param string $chosenTime
     * @param int $teacherId
     * @param int $labId
     * @param int $defenseLabId
     *
     * @throws RegistrationException
     */
    public function registerDefenceTime(
        int $studentId,
        int $submissionId,
        bool $ownTeacher,
        int $charonId,
        string $chosenTime,
        int $teacherId,
        int $labId,
        int $defenseLabId
    ) {
        $teacherCount = $this->teacherRepository->countLabTeachers($labId);
        $registeredSlotsAtTime = $this->defenseRegistrationRepository->countLabRegistrationsAt($labId, $chosenTime);

        if ($registeredSlotsAtTime >= $teacherCount) {
            throw new RegistrationException('invalid_chosen_time');
        }

        $user = $this->userRepository->find($studentId);

        try {
            $this->defenseRegistrationRepository->create([
                'student_name' => $user->firstname . ' ' . $user->lastname,
                'submission_id' => $submissionId,
                'choosen_time' => $chosenTime,
                'my_teacher' => $ownTeacher,
                'student_id' => $studentId,
                'defense_lab_id' => $defenseLabId,
                'progress' => 'Waiting',
                'charon_id' => $charonId,
                'teacher_id' => $teacherId
            ]);
        } catch (QueryException $exception) {
            if ($exception->errorInfo[1] == self::DUPLICATE_ERROR_CODE) {
                throw new RegistrationException('duplicate');
            }
            throw $exception;
        }

        Log::info(json_encode([
            'event' => 'registration_creation',
            'by_user_id' => $this->loggedInUser->currentUserId(),
            'for_user_id' => $studentId,
            'defense_lab_id' => $defenseLabId,
            'submission_id' => $submissionId,
            'chosen_time' => $chosenTime,
            'teacher_id' => $teacherId
        ]));
    }

    /**
     * @param string $time
     * @param int $charonId
     * @param int $labId
     * @param int $studentId
     * @param bool $ownTeacher
     *
     * @return array
     * @throws RegistrationException
     */
    public function getUsedDefenceTimes(string $time, int $charonId, int $labId, int $studentId, bool $ownTeacher): array
    {
        if ($ownTeacher) {
            $courseId = $this->charonRepository->getCharonById($charonId)->course;
            $teacher = $this->teacherRepository->getTeacherForStudent($studentId, $courseId);
            if ($teacher == null) {
                throw new RegistrationException('invalid_setup');
            }
            $labs = $this->defenseRegistrationRepository->getChosenTimesForTeacherAt($teacher->id, $time);
        } else {
            $teacherCount = $this->teacherRepository->countLabTeachers($labId);
            if ($teacherCount == 0) {
                throw new RegistrationException('invalid_setup');
            }
            $labs = $this->defenseRegistrationRepository->getChosenTimesForLabTeachers($time, $teacherCount, $labId);
        }

        $usedTimes = [];
        foreach ($labs as $lab) {
            $parts = explode(' ', $lab);
            $day_parts = explode(':', $parts[1]);
            array_push($usedTimes, $day_parts[0] . ":" . $day_parts[1]);
        }

        return $usedTimes;
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
            $lab->start,
            $lab->end
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
     * @param string $chosenTime
     *
     * @return int
     * @throws RegistrationException
     */
    public function getTeacherId(
        int $studentId,
        bool $ownTeacher,
        int $labId,
        int $charonId,
        string $chosenTime
    ): int {
        if (!$ownTeacher) {
            return $this->getTeachersByCharonAndLab($charonId, $labId, $chosenTime);
        }

        $courseId = $this->charonRepository->getCharonById($charonId)->course;
        $teacherId = $this->teacherRepository->getTeacherForStudent($studentId, $courseId)->id;

        $times = $this->defenseRegistrationRepository->getChosenTimesForTeacherAt($teacherId, $chosenTime);

        if (count($times) > 0) {
            throw new RegistrationException('teacher_is_busy');
        }
        return $teacherId;
    }

    /**
     * @param $charonId
     * @param $labId
     * @param $studentTime
     *
     * @return int
     * @throws RegistrationException
     */
    private function getTeachersByCharonAndLab($charonId, $labId, $studentTime): int
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
}
