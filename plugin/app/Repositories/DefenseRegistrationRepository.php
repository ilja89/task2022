<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

use Illuminate\Support\Facades\DB;
use TTU\Charon\Models\Registration;
use Zeizig\Moodle\Services\ModuleService;

/**
 * Class DefenseRegistrationRepository.
 * Used to handle database actions.
 *
 * @package TTU\Charon\Repositories
 */
class DefenseRegistrationRepository
{
    /** @var ModuleService */
    protected $moduleService;
    /**
     * @var LabTeacherRepository
     */
    protected $labTeacherRepository;

    /**
     * LabRepository constructor.
     *
     * @param ModuleService $moduleService
     * @param LabTeacherRepository $labTeacherRepository
     */
    public function __construct(ModuleService $moduleService, LabTeacherRepository $labTeacherRepository)
    {
        $this->moduleService = $moduleService;
        $this->labTeacherRepository = $labTeacherRepository;
    }

    /**
     * @return Builder|Registration
     */
    public function query()
    {
        return Registration::query();
    }

    /**
     * @param array $fields
     *
     * @return Registration
     */
    public function create($fields = [])
    {
        return Registration::create($fields);
    }

    /**
     * @param $teacherId
     * @param $time
     *
     * @return array
     */
    public function getChosenTimesForTeacherAt($teacherId, $time)
    {
        return DB::table('charon_defenders')
            ->where('choosen_time', 'like', '%' . $time . '%')
            ->where('teacher_id', $teacherId)
            ->pluck('choosen_time')
            ->all();
    }

    /**
     * @param string $time
     * @param int $teacherCount
     * @param int $defenseLabId
     *
     * @return array
     */
    public function getChosenTimesForAllTeachers(string $time, int $teacherCount, int $defenseLabId)
    {
        return DB::table('charon_defenders')
            ->where('choosen_time', 'like', '%' . $time . '%')
            ->where('defense_lab_id', $defenseLabId)
            ->groupBy('choosen_time')
            ->having(DB::raw('count(*)'), '=', $teacherCount)
            ->pluck('choosen_time')
            ->all();
    }

    /**
     * @param int $studentId
     * @param int $charonId
     * @param Carbon $labStart
     * @param Carbon $labEnd
     *
     * @return int
     */
    public function getUserPendingRegistrationsCount(int $studentId, int $charonId, Carbon $labStart, Carbon $labEnd)
    {
        return DB::table('charon_defenders')
            ->join('charon_defense_lab', 'charon_defense_lab.id', 'charon_defenders.defense_lab_id')
            ->join('charon_lab', 'charon_defense_lab.lab_id', 'charon_lab.id')
            ->where('charon_defense_lab.charon_id', $charonId)
            ->where('charon_defenders.student_id', $studentId)
            ->where('charon_defenders.progress', '!=', 'Done')
            ->whereBetween('charon_defenders.choosen_time', [date($labStart), date($labEnd)])
            ->select('charon_lab.id')
            ->count();
    }

    /**
     * Get defense registrations by course.
     * @param $courseId
     * @return Collection|Registration[]
     */
    public function getDefenseRegistrationsByCourse($courseId)
    {
        $defenseRegistrations = DB::table('charon_defenders')
            ->join('charon_submission', 'charon_submission.id', 'charon_defenders.submission_id')
            ->join('charon', 'charon.id', 'charon_submission.charon_id')
            ->join('charon_defense_lab', 'charon_defense_lab.id', 'charon_defenders.defense_lab_id')
            ->join('user', 'charon_defenders.teacher_id', 'user.id')
//            ->join('charon_lab', 'charon_lab.id', 'charon_defense_lab.lab_id') // not needed yet
            ->where('charon.course', $courseId)
            ->select(
                'charon_defenders.id', 'charon_defenders.choosen_time', 'charon_defenders.student_id',
                'charon_defenders.student_name', 'charon_submission.charon_id', 'charon.defense_duration',
                'charon_defenders.my_teacher', 'charon_defenders.submission_id', 'charon_defenders.progress',
                'charon_defense_lab.id as charon_defense_lab_id', 'charon_defenders.teacher_id',
                'user.firstname', 'user.lastname'
            )->orderBy('charon_defenders.choosen_time')
            ->get();

        return $this->moveTeacher($defenseRegistrations);
    }

    /**
     * Get defense registrations by course, filtered by after and before date.
     * @param $courseId
     * @param $after
     * @param $before
     * @param $teacher_id
     * @param $progress
     * @return Collection|Registration[]
     */
    public function getDefenseRegistrationsByCourseFiltered($courseId, $after, $before, $teacher_id, $progress)
    {
        if ($after != 'null' && $before != 'null') {
            $filteringWhere = sprintf(
                "choosen_time BETWEEN '%s' AND '%s'",
                Carbon::parse($after)->format('Y-m-d H:i:s'),
                Carbon::parse($before)->format('Y-m-d H:i:s')
            );
        } else if ($after != 'null') {
            $filteringWhere = "choosen_time >= '" . Carbon::parse($after)->format('Y-m-d H:i:s') . "'";
        } else if ($before != 'null') {
            $filteringWhere = "choosen_time <= '" . Carbon::parse($before)->format('Y-m-d H:i:s') . "'";
        } else {
            $filteringWhere = "student_id > '-1'";
        }
        $teacher_filter = "student_id > '-1'";
        if ($teacher_id != -1) {
            $teacher_filter = "teacher_id LIKE '" . $teacher_id . "'";
        }
        $progress_filter = "student_id > '-1'";
        if ($progress != 'null') {
            $progress_filter = "progress LIKE '" . $progress . "'";
        }

        $defenseRegistrations = DB::table('charon_defenders')
            ->join('charon_submission', 'charon_submission.id', 'charon_defenders.submission_id')
            ->join('charon', 'charon.id', 'charon_submission.charon_id')
            ->join('charon_defense_lab', 'charon_defense_lab.id', 'charon_defenders.defense_lab_id')
            ->join('user', 'charon_defenders.teacher_id', 'user.id')
            ->where('charon.course', $courseId)
            ->whereRaw($filteringWhere)
            ->whereRaw($teacher_filter)
            ->whereRaw($progress_filter)
            ->select('charon_defenders.id', 'charon_defenders.choosen_time', 'charon_defenders.student_id',
                'charon_defenders.student_name', 'charon_submission.charon_id', 'charon.defense_duration',
                'charon_defenders.my_teacher', 'charon_defenders.submission_id', 'charon_defenders.progress',
                'charon_defense_lab.id as charon_defense_lab_id', 'charon_defenders.teacher_id',
                'user.firstname', 'user.lastname'
            )->orderBy('charon_defenders.choosen_time')
            ->get();

        return $this->moveTeacher($defenseRegistrations);
    }

    /**
     * @param $defenseRegistrations
     * @return mixed
     */
    private function moveTeacher($defenseRegistrations)
    {
        for ($i = 0; $i < count($defenseRegistrations); $i++) {
            $teacher = [];
            $teacher['id'] = $defenseRegistrations[$i]->teacher_id;
            $teacher['firstname'] = $defenseRegistrations[$i]->firstname;
            $teacher['lastname'] = $defenseRegistrations[$i]->lastname;
            $teacher['fullname'] = $defenseRegistrations[$i]->firstname . " " . $defenseRegistrations[$i]->lastname;
            unset($defenseRegistrations[$i]->teacher_id);
            unset($defenseRegistrations[$i]->firstname);
            unset($defenseRegistrations[$i]->lastname);
            $defenseRegistrations[$i]->teacher = $teacher;
        }

        return $defenseRegistrations;
    }

    /**
     * Save defending progress.
     * @param $defenseId
     * @param $newProgress
     * @param $newTeacherId
     * @return Registration
     */
    public function updateRegistration($defenseId, $newProgress, $newTeacherId)
    {
        $defense = Registration::find($defenseId);
        $defense->progress = $newProgress;
        $defense->teacher_id = $newTeacherId;
        $defense->update();
        return $defense;
    }

    public function deleteRegistration($studentId, $defenseLabId, $submissionId)
    {
        return DB::table('charon_defenders')
            ->where('student_id', $studentId)
            ->where('defense_lab_id', $defenseLabId)
            ->where('submission_id', $submissionId)
            ->delete();
    }

    public function getStudentRegistrations($studentId)
    {
        return DB::table('charon_defenders')
            ->where('charon_defenders.student_id', $studentId)
            ->join('charon', 'charon.id', '=', 'charon_defenders.charon_id')
            ->join('user', 'charon_defenders.teacher_id', 'user.id')
            ->join('charon_defense_lab', 'charon_defenders.defense_lab_id', 'charon_defense_lab.id')
            ->join('charon_lab_teacher', 'charon_lab_teacher.teacher_id', 'charon_defenders.teacher_id')
            ->select(DB::raw('CONCAT(firstname, " ", lastname) AS teacher'))
            ->addSelect('charon.name', 'charon_defenders.choosen_time', 'charon_defenders.teacher_id',
                'charon_defenders.submission_id', 'charon_defenders.defense_lab_id',
                'charon_lab_teacher.teacher_location', 'charon_lab_teacher.teacher_comment')
            ->distinct()
            ->get();
    }

}
