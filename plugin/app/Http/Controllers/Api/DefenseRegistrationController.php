<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Registration;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Repositories\DefenseRegistrationRepository;
use TTU\Charon\Repositories\StudentsRepository;
use Zeizig\Moodle\Globals\User;
use Zeizig\Moodle\Models\Course;

class DefenseRegistrationController extends Controller
{
    /** @var DefenseRegistrationRepository */
    private $defenseRegistrationRepository;

    /** @var StudentsRepository */
    protected $studentsRepository;

    /**
     * LabDummyController constructor.
     *
     * @param Request $request
     * @param StudentsRepository $studentsRepository
     * @param DefenseRegistrationRepository $defenseRegistrationRepository
     */
    public function __construct(Request $request,
                                StudentsRepository $studentsRepository,
                                DefenseRegistrationRepository $defenseRegistrationRepository)
    {
        parent::__construct($request);
        $this->studentsRepository = $studentsRepository;
        $this->defenseRegistrationRepository = $defenseRegistrationRepository;
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
     * @param $teacher_id
     * @param $progress
     * @return Collection|Registration[]
     */
    public function getDefenseRegistrationsByCourseFiltered(Course $course, $after, $before, $teacher_id, $progress)
    {
        return $this->defenseRegistrationRepository->getDefenseRegistrationsByCourseFiltered($course->id, $after, $before, $teacher_id, $progress);
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

    public function deleteReg(Request $request)
    {
        $student_id = $request->input('user_id');
        $defense_lab_id = $request->input('defLab_id');
        $submission_id = $request->input('submission_id');

        Log::warning(json_encode([
            'event' => 'registration_deletion',
            'by_user_id' => app(User::class)->currentUserId(),
            'for_user_id' => $student_id,
            'defense_lab_id' => $defense_lab_id,
            'submission_id' => $submission_id
        ]));

        return $this->defenseRegistrationRepository->deleteRegistration($student_id, $defense_lab_id, $submission_id);
    }

    public function getStudentRegistrations(Request $request)
    {
        $student_id = $request->input('user_id');

        return $this->defenseRegistrationRepository->getStudentRegistrations($student_id);

    }

}
