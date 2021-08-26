<?php

namespace TTU\Charon\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use stdClass;
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

    /** @var DefenseRegistrationRepository */
    private $defenseRegistrationRepository;

    /** @var MoodleUser */
    private $loggedInUser;

    /** @var UserRepository */
    private $userRepository;

    /** @var ConverterService */
    private $converterService;

    /**
     * DefenceRegistrationService constructor.
     * @param CharonRepository $charonRepository
     * @param LabTeacherRepository $teacherRepository
     * @param DefenseRegistrationRepository $defenseRegistrationRepository
     * @param MoodleUser $loggedInUser
     * @param UserRepository $userRepository
     * @param ConverterService $converterService
     */
    public function __construct(
        CharonRepository $charonRepository,
        LabTeacherRepository $teacherRepository,
        DefenseRegistrationRepository $defenseRegistrationRepository,
        MoodleUser $loggedInUser,
        UserRepository $userRepository,
        ConverterService $converterService
    ) {
        $this->charonRepository = $charonRepository;
        $this->teacherRepository = $teacherRepository;
        $this->defenseRegistrationRepository = $defenseRegistrationRepository;
        $this->loggedInUser = $loggedInUser;
        $this->userRepository = $userRepository;
        $this->converterService = $converterService;
    }

    /**
     * Student registers for a defence
     *
     * @param int $studentId
     * @param int $submissionId
     * @param int $charonId
     * @param int $defenseLabId
     */
    public function registerDefenceTime(
        int $studentId,
        int $submissionId,
        int $charonId,
        int $defenseLabId
    ) {
        $user = $this->userRepository->find($studentId);

        $this->defenseRegistrationRepository->create([
            'student_name' => $user->firstname . ' ' . $user->lastname,
            'submission_id' => $submissionId,
            'student_id' => $studentId,
            'defense_lab_id' => $defenseLabId,
            'progress' => 'Waiting',
            'charon_id' => $charonId,
        ]);

        Log::info(json_encode([
            'event' => 'registration_creation',
            'by_user_id' => $this->loggedInUser->currentUserId(),
            'for_user_id' => $studentId,
            'defense_lab_id' => $defenseLabId,
            'submission_id' => $submissionId,
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
            $lab->start,
            $lab->end
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

    /** Function to defer any existing registration to last place in queue
     * @param $userId
     * @param $defLabId
     * @param $charonId
     * @param $submissionId
     * @param $regId
     * @return false|string
     */
    public function deferRegistration($userId, $defLabId, $charonId, $submissionId, $regId)
    {
        $result = new stdClass();

        //0. Check if all required info received
        if(
            $userId == null||
            $defLabId == null||
            $charonId == null||
            $submissionId == null||
            $regId == null
            ) {
            $result->okay = false;
            $result->reason = "One of fields received in deferRegistration(userId = $userId, defLabId = $defLabId, charonId = $charonId, submissionId = $submissionId, regId = $regId) is null";
            return json_encode($result);
        }

        $result->input["userId"] = $userId; //DEBUG!
        $result->input["defLabId"] = $defLabId; //DEBUG!
        $result->input["charonId"] = $charonId; //DEBUG!
        $result->input["submissionId"] = $submissionId; //DEBUG!
        $result->input["regId"] = $regId; //DEBUG!

        //1. Check if this request is acceptable at all.
        //1.1 Get id of student what is registered for this lab and id of teachers what are related to this lab
        $allowed = array_merge(
            $this->defenseRegistrationRepository->getStudentIdForDefenceRegistration($regId),
            $this->teacherRepository->getTeachersRelatedToDefenceLab($defLabId)
        );

        $result->allowed = $allowed; //DEBUG!
        //1.2 Check if any of these IDs is similar to id of user trying to defer this registration
        foreach ($allowed as $var) {
            if($var->id == $userId) {
                $allowed = true;
            }
        }

        //1.3 If it is not and this user is not allowed to defer this registration, then disapprove
        if($allowed !== true) {
            $result->okay = false;
            $result->reason = "User with userId = $userId is not student related to this registration or teacher related to this lab.";
            return json_encode($result);
        }

        //2. Put registration in the end of queue by deleting old one and creating new one
        //2.1 Get all info about this registration
        $reg = $this->defenseRegistrationRepository->getDefenseRegistrationByRegId($regId);

        $reg = $this->converterService->objectToArray($reg);
        unset($reg['id']);

        //2.2  Delete old registration
        $this->defenseRegistrationRepository->deleteRegistrationById($regId);

        //2.2 Create a new registration using vars received
        $newRegId = $this->defenseRegistrationRepository->create($reg)->id;

        $result->reg = $reg; //DEBUG!
        $result->allowed = $allowed; //DEBUG!
        $result->okay = true;
        $result->newRegId = $newRegId;
        return json_encode($result);
    }
}
