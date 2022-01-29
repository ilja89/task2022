<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Exceptions\RegistrationException;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Registration;
use TTU\Charon\Repositories\CharonDefenseLabRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\StudentsRepository;
use TTU\Charon\Services\DefenceRegistrationService;
use TTU\Charon\Services\LabService;
use Zeizig\Moodle\Globals\User;
use Zeizig\Moodle\Models\Course;

class DefenseRegistrationController extends Controller
{
    /** @var DefenseRegistrationRepository */
    private $defenseRegistrationRepository;

    /** @var StudentsRepository */
    protected $studentsRepository;

    /** @var DefenceRegistrationService */
    protected $defenceRegistrationService;

    /** @var LabService */
    protected $labService;

    /** @var CharonDefenseLabRepository */
    protected $defenseLabRepository;

    /**
     * DefenseRegistrationController constructor.
     *
     * @param Request $request
     * @param StudentsRepository $studentsRepository
     * @param DefenseRegistrationRepository $defenseRegistrationRepository
     * @param DefenceRegistrationService $defenceRegistrationService
     * @param LabService $labService
     * @param CharonDefenseLabRepository $defenseLabRepository
     */
    public function __construct(
        Request $request,
        StudentsRepository $studentsRepository,
        DefenseRegistrationRepository $defenseRegistrationRepository,
        DefenceRegistrationService $defenceRegistrationService,
        LabService $labService,
        CharonDefenseLabRepository $defenseLabRepository
    ) {
        parent::__construct($request);
        $this->studentsRepository = $studentsRepository;
        $this->defenseRegistrationRepository = $defenseRegistrationRepository;
        $this->defenceRegistrationService = $defenceRegistrationService;
        $this->labService = $labService;
        $this->defenseLabRepository = $defenseLabRepository;
    }

    /**
     * @throws RegistrationException
     */
    public function registerDefenceByStudent(Request $request): string
    {
        return $this->defenceRegistrationService->registerDefence(
            $request->input("user_id"),
            $request->input("charon_id"),
            $request->input("defense_lab_id"),
            $request->input('submission_id')
        );
    }

    /**
     * @throws RegistrationException
     */
    public function registerDefenceByTeacher(Request $request): string
    {
        return $this->defenceRegistrationService->registerDefence(
            $request->input("user_id"),
            $request->input("charon_id"),
            $request->input("defense_lab_id"),
            null
        );
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
     * Get defense registrations by course filtered by time, teacher, progress.
     * If session started, then return defense registrations which has no teacher or/and has current user as teacher.
     * @param Course $course
     * @return Registration[]
     */
    public function getDefenseRegistrationsByCourseFiltered(Course $course)
    {
        return $this->defenceRegistrationService->getDefenseRegistrationsByCourseFiltered(
            $course->id,
            $this->request['after'] ? $this->request['after'] : null,
            $this->request['before'] ? $this->request['before'] : null,
            $this->request['teacher_id'] ? $this->request['teacher_id'] : null,
            $this->request['progress'] ? $this->request['progress'] : null,
            $this->request['session'] == 'true'
        );
    }

    /**
     * Updates registration.
     * Decline registration updating if user is not lab teacher of lab in which registration belongs to.
     *
     * @param Course $course
     * @param Registration $registration
     * @return Registration
     * @throws RegistrationException
     */
    public function updateRegistration(Course $course, Registration $registration): Registration
    {
        return $this->defenceRegistrationService->updateRegistration(
            $registration->id,
            $this->request['progress'],
            $this->request['teacher_id']
        );
    }

    /**
     * @param Request $request
     * @return int
     */
    public function delete(Request $request)
    {
        $studentId = $request->input('user_id');
        $defenseLabId = $request->input('defLab_id');
        $submissionId = $request->input('submission_id');

        Log::info(json_encode([
            'event' => 'registration_deletion',
            'by_user_id' => app(User::class)->currentUserId(),
            'for_user_id' => $studentId,
            'defense_lab_id' => $defenseLabId,
            'submission_id' => $submissionId
        ]));

        return $this->defenseRegistrationRepository->deleteRegistration($studentId, $defenseLabId, $submissionId);
    }

    /**
     * @param Request $request
     * @return Collection
     */
    public function getStudentRegistrations(Request $request): Collection
    {
        return $this->defenseRegistrationRepository->getStudentRegistrations($request->input('user_id'));
    }

    /**
     * Return teacher' registration with progress 'Defending'
     *
     * @param Request $request
     * @return JsonResponse|null
     */
    public function getLabTeacherActiveRegistration(Request $request): ?JsonResponse
    {
        $teacher = $request->input('teacher_id');
        if ($teacher == "null") {
            $teacher = app(User::class)->currentUserId();
        }
        return $this->defenseRegistrationRepository->getLabTeacherActiveRegistration($request->input('lab_id'), $teacher);
    }

    /**
     * Searches for all teacher' registrations with status 'Defending' and
     * puts them new progress (activeRegistrationsProgress). Then updates given registration with
     * new progress (registrationNewProgress).
     *
     * @param Course $course
     * @param Registration $registration
     * @return Registration
     * @throws RegistrationException
     */
    public function updateRegistrationProgressAndUnDefendRegistrationsByTeacher(
        Course $course,
        Registration $registration
    ): Registration
    {
        return $this->defenceRegistrationService->updateRegistrationProgressAndUnDefendRegistrationsByTeacher(
            $registration,
            $this->request['teacher_id'],
            $this->request['lab_id'],
            $this->request['activeRegistrationsProgress'],
            $this->request['registrationNewProgress']
        );
    }
}
