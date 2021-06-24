<?php

namespace TTU\Charon\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use TTU\Charon\Exceptions\BadRequestException;
use TTU\Charon\Exceptions\RegistrationException;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Registration;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Repositories\CharonDefenseLabRepository;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\StudentsRepository;
use TTU\Charon\Services\DefenceRegistrationService;
use TTU\Charon\Services\Flows\BookStudentRegistration;
use TTU\Charon\Services\Flows\FindAvailableRegistrationTimes;
use Zeizig\Moodle\Globals\User;
use Zeizig\Moodle\Models\Course;
use TTU\Charon\Repositories\LabTeacherRepository;

class DefenseRegistrationController extends Controller
{
    /** @var DefenseRegistrationRepository */
    private $defenseRegistrationRepository;

    /** @var CharonRepository */
    protected $charonRepository;

    /** @var StudentsRepository */
    protected $studentsRepository;

    /** @var DefenceRegistrationService */
    protected $registrationService;

    /** @var CharonDefenseLabRepository */
    protected $defenseLabRepository;

    /** @var LabTeacherRepository */
    protected $labTeacherRepository;

    /** @var FindAvailableRegistrationTimes */
    protected $findTimes;

    /** @var BookStudentRegistration */
    protected $bookRegistration;

    /**
     * DefenseRegistrationController constructor.
     *
     * @param Request $request
     * @param CharonRepository $charonRepository
     * @param StudentsRepository $studentsRepository
     * @param DefenseRegistrationRepository $defenseRegistrationRepository
     * @param DefenceRegistrationService $registrationService
     * @param CharonDefenseLabRepository $defenseLabRepository
     * @param FindAvailableRegistrationTimes $findTimes
     * @param BookStudentRegistration $bookRegistration
     */
    public function __construct(
        Request $request,
        CharonRepository $charonRepository,
        StudentsRepository $studentsRepository,
        DefenseRegistrationRepository $defenseRegistrationRepository,
        DefenceRegistrationService $registrationService,
        CharonDefenseLabRepository $defenseLabRepository,
        FindAvailableRegistrationTimes $findTimes,
        BookStudentRegistration $bookRegistration
    ) {
        parent::__construct($request);
        $this->charonRepository = $charonRepository;
        $this->studentsRepository = $studentsRepository;
        $this->defenseRegistrationRepository = $defenseRegistrationRepository;
        $this->registrationService = $registrationService;
        $this->defenseLabRepository = $defenseLabRepository;
        $this->findTimes = $findTimes;
        $this->bookRegistration = $bookRegistration;
    }

    /**
     * @version Registration 2.*
     *
     * @return array
     * @throws ValidationException
     */
    public function findAvailableTimes(): array
    {
        $courseId = $this->charonRepository->getCharonById($this->request->input('charon_id'))->course;

        $validator = Validator::make($this->request->all(), [
            'submissions' => 'required|filled',
            'student' => 'required|integer|filled',
            'start' => 'required|date|after:' . Carbon::now(),
            'end' => 'required|date|after:start',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->findTimes->run(
            $courseId,
            $this->request->input('student'),
            $this->request->input('submissions'),
            Carbon::parse($this->request->input('start')),
            Carbon::parse($this->request->input('end'))
        );
    }

    /**
     * @version Registration 2.*
     *
     * @return bool
     * @throws ValidationException
     */
    public function bookRegistrationTime(): bool
    {
        $validator = Validator::make($this->request->all(), [
            'course' => 'required|integer|filled',
            'lab' => 'required|integer|filled',
            'student' => 'required|integer|filled',
            'charon' => 'required|integer|filled',
            'submission' => 'required|integer|filled',
            'start' => 'required|date|after:' . Carbon::now(),
            'end' => 'required|date|after:start',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->bookRegistration->run(
            $this->request->input('course'),
            $this->request->input('lab'),
            $this->request->input('student'),
            $this->request->input('charon'),
            $this->request->input('submission'),
            Carbon::parse($this->request->input('start')),
            Carbon::parse($this->request->input('end'))
        );
    }

    /**
     * Student registers for a defence time slot
     *
     * @version Registration 1.*
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
        $ownTeacher = $request->input('selected') == 1;
        $charonId = $request->input('charon_id');
        $chosenTime = $request->input('student_chosen_time');
        $defenseLabId = $request->input('defense_lab_id');

        $lab = $this->defenseLabRepository->getLabByDefenseLabId($defenseLabId);

        $this->registrationService->validateDefence($studentId, $charonId, $chosenTime, $lab);

        $teacherId = $this->registrationService->getTeacherId(
            $studentId,
            $ownTeacher,
            $lab->id,
            $charonId,
            Carbon::parse($chosenTime)
        );

        $this->registrationService->registerDefenceTime(
            $studentId,
            $submissionId,
            $ownTeacher,
            $charonId,
            $chosenTime,
            $teacherId,
            $lab->id,
            $defenseLabId
        );

        return 'inserted';
    }

    /**
     * Currently the whole lab starts off as available, this endpoint reveals which slots have already been taken
     *
     * lab_id refers to CharonDefenseLab->id
     *
     * @version Registration 1.*
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
     *
     * @version Registration 1.*
     *
     * @param Course $course
     *
     * @return Collection|Registration[]
     */
    public function getDefenseRegistrationsByCourse(Course $course)
    {
        return $this->defenseRegistrationRepository->getDefenseRegistrationsByCourse($course->id);
    }

    /**
     * Get defense registrations by course, filtered by before and after date.
     *
     * @version Registration 1.*
     *
     * @param Course $course
     * @param $after
     * @param $before
     * @param $teacherId
     * @param $progress
     *
     * @return Collection|Registration[]
     */
    public function getDefenseRegistrationsByCourseFiltered(Course $course, $after, $before, $teacherId, $progress)
    {
        return $this->defenseRegistrationRepository->getDefenseRegistrationsByCourseFiltered($course->id, $after, $before, $teacherId, $progress);
    }

    /**
     * Save defense progress.
     *
     * @version Registration 1.*
     *
     * @param Course $course
     * @param Registration $registration
     *
     * @return Registration
     */
    public function saveProgress(Course $course, Registration $registration)
    {
        return $this->defenseRegistrationRepository->updateRegistration($registration->id, $this->request['progress'], $this->request['teacher_id']);
    }

    /**
     * @version Registration 1.*
     *
     * @param Request $request
     *
     * @return mixed
     */
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

    /**
     * @version Registration 1.*
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function getStudentRegistrations(Request $request)
    {
        $studentId = $request->input('user_id');

        return $this->defenseRegistrationRepository->getStudentRegistrations($studentId);
    }

    /**
     * @param $courseId
     * @param $studentId
     * @param $registrations
     * @return string
     * @throws BadRequestException
     * @version Registration 1.*
     */
    public function register($courseId, $studentId, $registrations) : string
    {
        $currentUserId = (new User)->currentUserId();
        $teachers = $this->labTeacherRepository->getTeachersByCourseId($courseId);
        $student = $this->studentsRepository->searchStudentsByCourseAndKeyword($courseId, $currentUserId);

        if ($currentUserId == $studentId && $student::id == $studentId || in_array($currentUserId, $teachers) ){
            $result = $this->defenseRegistrationRepository->register($studentId, $registrations);
            if ($result != "success")
            {
                return $result;
            }
            return "";
        }
        throw new BadRequestException("Either student or teacher not registered in the course");
    }
}
