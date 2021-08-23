<?php

namespace TTU\Charon\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use TTU\Charon\Exceptions\RegistrationException;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Registration;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Repositories\CharonDefenseLabRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\StudentsRepository;
use TTU\Charon\Services\DefenceRegistrationService;
use Zeizig\Moodle\Globals\User;
use Zeizig\Moodle\Models\Course;

class DefenseRegistrationController extends Controller
{
    /** @var DefenseRegistrationRepository */
    private $defenseRegistrationRepository;

    /** @var StudentsRepository */
    protected $studentsRepository;

    /** @var DefenceRegistrationService */
    protected $registrationService;

    /** @var CharonDefenseLabRepository */
    protected $defenseLabRepository;

    /**
     * DefenseRegistrationController constructor.
     *
     * @param Request $request
     * @param StudentsRepository $studentsRepository
     * @param DefenseRegistrationRepository $defenseRegistrationRepository
     * @param DefenceRegistrationService $registrationService
     * @param CharonDefenseLabRepository $defenseLabRepository
     */
    public function __construct(
        Request $request,
        StudentsRepository $studentsRepository,
        DefenseRegistrationRepository $defenseRegistrationRepository,
        DefenceRegistrationService $registrationService,
        CharonDefenseLabRepository $defenseLabRepository
    ) {
        parent::__construct($request);
        $this->studentsRepository = $studentsRepository;
        $this->defenseRegistrationRepository = $defenseRegistrationRepository;
        $this->registrationService = $registrationService;
        $this->defenseLabRepository = $defenseLabRepository;
    }

    /**
     * Student registers for a defence time slot
     *
     * @param Request $request
     *
     * @return string
     * @throws RegistrationException
     */
    public function studentRegisterDefence(Request $request): string
    {
        $studentId = $request->input('user_id');
        $submissionId = $request->input('submission_id');
        $charonId = $request->input('charon_id');
        $defenseLabId = $request->input('defense_lab_id');

        $lab = $this->defenseLabRepository->getLabByDefenseLabId($defenseLabId);
        $this->registrationService->validateRegistration($studentId, $charonId, $lab);

        $this->registrationService->registerDefenceTime(
            $studentId,
            $submissionId,
            $charonId,
            $defenseLabId
        );

        return 'inserted';
    }

    /**
     * Get defense registrations by course.
     * @param Course $course
     * @return Collection|Registration[]
     */
    public function getDefenseRegistrationsByCourse(Course $course)
    {
        return $this->defenseRegistrationRepository->getDefenseRegistrationsByCourse($course->id);
    }

    /**
     * Get defense registrations by course, filtered by before and after date.
     * @param Course $course
     * @param $after
     * @param $before
     * @param $teacherId
     * @param $progress
     * @return Collection|Registration[]
     */
    public function getDefenseRegistrationsByCourseFiltered(Course $course, $after, $before, $teacherId, $progress)
    {
        return $this->defenseRegistrationRepository->getDefenseRegistrationsByCourseFiltered($course->id, $after, $before, $teacherId, $progress);
    }

    /**
     * Save defense progress.
     * @param Course $course
     * @param Registration $registration
     * @return Registration
     */
    public function saveProgress(Course $course, Registration $registration)
    {
        return $this->defenseRegistrationRepository->updateRegistration($registration->id, $this->request['progress'], $this->request['teacher_id']);
    }

    public function delete(Request $request)
    {
        $studentId = $request->input('user_id');
        $defenseLabId = $request->input('defLab_id');
        $submissionId = $request->input('submission_id');

        Log::warning(json_encode([
            'event' => 'registration_deletion',
            'by_user_id' => app(User::class)->currentUserId(),
            'for_user_id' => $studentId,
            'defense_lab_id' => $defenseLabId,
            'submission_id' => $submissionId
        ]));

        return $this->defenseRegistrationRepository->deleteRegistration($studentId, $defenseLabId, $submissionId);
    }

    public function getStudentRegistrations(Request $request)
    {
        $studentId = $request->input('user_id');

        return $this->defenseRegistrationRepository->getStudentRegistrations($studentId);
    }
}
