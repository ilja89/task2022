<?php

namespace TTU\Charon\Http\Controllers\Api;

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
     * @param DefenceRegistrationService $registrationService
     * @param LabService $labService
     * @param CharonDefenseLabRepository $defenseLabRepository
     */
    public function __construct(
        Request $request,
        StudentsRepository $studentsRepository,
        DefenseRegistrationRepository $defenseRegistrationRepository,
        DefenceRegistrationService $registrationService,
        LabService $labService,
        CharonDefenseLabRepository $defenseLabRepository
    ) {
        parent::__construct($request);
        $this->studentsRepository = $studentsRepository;
        $this->defenseRegistrationRepository = $defenseRegistrationRepository;
        $this->defenceRegistrationService = $registrationService;
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
            null,
            $request->input("progress")
        );
    }

    /**
     * Currently the whole lab starts off as available, this endpoint reveals which slots have already been taken
     *
     * lab_id refers to CharonDefenseLab->id
     *
     * @param Request $request
     *
     * @return array
     * @throws RegistrationException
     */
    public function getUsedDefenceTimes(Request $request): array
    {
        $lab = $this->defenseLabRepository->getLabByDefenseLabId($request->input('lab_id'));

        return $this->defenceRegistrationService->getUsedDefenceTimes(
            $request->input('time'),
            $request->input('charon_id'),
            $lab,
            $request->input('user_id'),
            $request->input('my_teacher') == 'true'
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
     * Get defense registrations by course, filtered by before and after date.
     * @param Course $course
     * @param $after
     * @param $before
     * @param $teacherId
     * @param $progress
     * @return Registration[]
     */
    public function getDefenseRegistrationsByCourseFiltered(Course $course, $after, $before, $teacherId, $progress)
    {
        return $this->defenceRegistrationService->getDefenseRegistrationsByCourseFiltered($course->id, $after, $before, $teacherId, $progress);
    }

    /**
     * Save defense progress.
     * @param Course $course
     * @param Registration $registration
     * @return Registration
     */
    public function saveProgress(Course $course, Registration $registration)
    {
        return $this->defenceRegistrationService->updateRegistration($registration->id, $this->request['progress'], $this->request['teacher_id']);
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

    public function getLabTeacherActiveRegistrations(Request $request)
    {
        return $this->defenseRegistrationRepository->getLabTeacherActiveRegistrations($request->input('lab_id'), $request->input('teacher_id'));
    }

    public function updateRegistrationProgressAndUnDefendRegistrationsByTeacher(Course $course, Registration $registration): Registration
    {
        $this->defenseRegistrationRepository->updateRegistrationsProgressByTeacherAndLab($this->request['lab_id'], $registration->teacher_id, $this->request['registrationsProgress']);
        return $this->defenseRegistrationRepository->updateRegistrationProgress($registration->id, $this->request['registrationProgress']);
    }
}
