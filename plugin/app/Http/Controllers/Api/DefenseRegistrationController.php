<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use TTU\Charon\Exceptions\RegistrationException;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Registration;
use TTU\Charon\Repositories\CharonDefenseLabRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\StudentsRepository;
use TTU\Charon\Services\DefenceRegistrationService;
use TTU\Charon\Services\LabService;
use Zeizig\Moodle\Models\Course;

class DefenseRegistrationController extends Controller
{
    /** @var DefenseRegistrationRepository */
    private $defenseRegistrationRepository;

    /** @var StudentsRepository */
    protected $studentsRepository;

    /** @var DefenceRegistrationService */
    protected $registrationService;

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
        $this->registrationService = $registrationService;
        $this->labService = $labService;
        $this->defenseLabRepository = $defenseLabRepository;
    }

    /**
     * @throws RegistrationException
     */
    public function registerDefenceByStudent(Request $request): string
    {
        return $this->registrationService->registerDefence(
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
        return $this->registrationService->registerDefence(
            $request->input("user_id"),
            $request->input("charon_id"),
            $request->input("defense_lab_id"),
            null
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

        return $this->registrationService->getUsedDefenceTimes(
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
     * Get defense registrations by course filtered by time, teacher, progress.
     * If session started, then return defense registrations which has no teacher or/and has current user as teacher.
     * @param Course $course
     * @return Registration[]
     */
    public function getDefenseRegistrationsByCourseFiltered(Course $course)
    {
        return $this->registrationService->getDefenseRegistrationsByCourseFiltered(
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
        return $this->registrationService->updateRegistration(
            $registration->id,
            $this->request['progress'],
            $this->request['teacher_id']
        );
    }

    /**
     * @throws RegistrationException
     */
    public function delete(Request $request)
    {
        return $this->registrationService->delete(
            $request->input('user_id'),
            $request->input('defLab_id'),
            $request->input('submission_id')
        );
    }

    public function getStudentRegistrations(Request $request)
    {
        $studentId = $request->input('user_id');

        return $this->defenseRegistrationRepository->getStudentRegistrations($studentId);
    }
}
