<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Models\DefenseRegistration;
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
     * @version Registration 2.*
     *
     * @param Carbon $from
     * @param Carbon $to
     * @param int|null $excludingLab
     *
     * @return int[]
     */
    public function checkBusyTeachersBetween(Carbon $from, Carbon $to, int $excludingLab = null): array
    {
        $query = DefenseRegistration::query()
            ->select('teacher_id')
            ->whereDate('time', '>=', $from->format('Y-m-d'))
            ->whereDate('time', '<=', $to->format('Y-m-d'))
            ->whereTime('time', '>=', $from->toTimeString())
            ->whereTime('time', '<=', $to->toTimeString());

        if ($excludingLab) {
            $query = $query->where('lab_id', '<>', $excludingLab);
        }

        return $query->get()->pluck('teacher_id')->unique()->all();
    }

    /**
     * @version Registration 1.*
     *
     * @return Builder|Registration
     */
    public function query()
    {
        return Registration::query();
    }

    /**
     * @version Registration 2.*
     *
     * @param array $collection
     */
    public function createMany(array $collection)
    {
        DefenseRegistration::insert(array_map(function ($registration) {
            return $registration + [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }, $collection));
    }

    /**
     * @version Registration 1.*
     *
     * @param array $fields
     *
     * @return Registration
     */
    public function create($fields = [])
    {
        return Registration::create($fields);
    }

    /**
     * @version Registration 1.*
     *
     * @param int $labId
     * @param Carbon $time
     *
     * @return int
     */
    public function countLabRegistrationsAt(int $labId, Carbon $time): int
    {
        return DB::table('charon_defenders')
            ->join('charon_defense_lab', 'charon_defense_lab.id', 'charon_defenders.defense_lab_id')
            ->join('charon', 'charon.id', 'charon_defenders.charon_id')
            ->whereDate('charon_defenders.choosen_time', '=', $time->format('Y-m-d'))
            ->whereTime('charon_defenders.choosen_time', '<=', $time->toTimeString())
            ->whereTime(DB::raw('choosen_time + INTERVAL defense_duration MINUTE'), '>', $time->toTimeString())
            ->where('charon_defense_lab.lab_id', $labId)
            ->count();
    }

    /**
     * @version Registration 1.*
     *
     * @param int $teacherId
     * @param Carbon $time
     *
     * @return bool
     */
    public function isTeacherBusyAt(int $teacherId, Carbon $time): bool
    {
        return DB::table('charon_defenders')
            ->join('charon', 'charon.id', 'charon_defenders.charon_id')
            ->whereDate('charon_defenders.choosen_time', '=', $time->format('Y-m-d'))
            ->whereTime('charon_defenders.choosen_time', '<=', $time->toTimeString())
            ->whereTime(DB::raw('choosen_time + INTERVAL defense_duration MINUTE'), '>', $time->toTimeString())
            ->where('teacher_id', $teacherId)
            ->count() > 0;
    }

    /**
     * @version Registration 1.*
     *
     * @param $teacherId
     * @param $time
     *
     * @return array
     */
    public function getChosenTimesForTeacherAt($teacherId, $time): array
    {
        return DB::table('charon_defenders')
            ->join('charon', 'charon.id', 'charon_defenders.charon_id')
            ->where('choosen_time', 'like', '%' . $time . '%')
            ->where('teacher_id', $teacherId)
            ->select('choosen_time', 'defense_duration')
            ->get()
            ->all();
    }

    /**
     * @version Registration 1.*
     *
     * @param string $time
     * @param int $labId
     *
     * @return array
     */
    public function getChosenTimesForLabTeachers(string $time, int $labId): array
    {
        return DB::table('charon_defenders')
            ->join('charon_defense_lab', 'charon_defense_lab.id', 'charon_defenders.defense_lab_id')
            ->join('charon', 'charon.id', 'charon_defenders.charon_id')
            ->where('choosen_time', 'like', '%' . $time . '%')
            ->where('charon_defense_lab.lab_id', $labId)
            ->select('choosen_time', 'defense_duration')
            ->get()
            ->all();
    }

    /**
     * @version Registration 1.*
     *
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
     *
     * @version Registration 1.*
     *
     * @param $courseId
     *
     * @return Collection|Registration[]
     */
    public function getDefenseRegistrationsByCourse($courseId)
    {
        $defenseRegistrations = DB::table('charon_defenders')
            ->join('charon_submission', 'charon_submission.id', 'charon_defenders.submission_id')
            ->join('charon', 'charon.id', 'charon_submission.charon_id')
            ->join('charon_defense_lab', 'charon_defense_lab.id', 'charon_defenders.defense_lab_id')
            ->join('user', 'charon_defenders.teacher_id', 'user.id')
            ->join('charon_lab', 'charon_lab.id', 'charon_defense_lab.lab_id')
            ->where('charon.course', $courseId)
            ->select(
                'charon_defenders.id', 'charon_defenders.choosen_time', 'charon_defenders.student_id',
                'charon_defenders.student_name', 'charon_submission.charon_id', 'charon.defense_duration',
                'charon_defenders.my_teacher', 'charon_defenders.submission_id', 'charon_defenders.progress',
                'charon_defense_lab.id as charon_defense_lab_id', 'charon_defenders.teacher_id',
                'user.firstname', 'user.lastname', 'charon_lab.name as lab_name'
            )->orderBy('charon_defenders.choosen_time')
            ->get();

        return $this->moveTeacher($defenseRegistrations);
    }

    /**
     * Get defense registrations by course, filtered by after and before date.
     *
     * @version Registration 1.*
     *
     * @param $courseId
     * @param $after
     * @param $before
     * @param $teacher_id
     * @param $progress
     *
     * @return Collection|Registration[]
     */
    public function getDefenseRegistrationsByCourseFiltered($courseId, $after, $before, $teacher_id, $progress)
    {
        /** @var \Illuminate\Database\Query\Builder $query */
        $query = DB::table('charon_defenders')
            ->join('charon_submission', 'charon_submission.id', 'charon_defenders.submission_id')
            ->join('charon', 'charon.id', 'charon_submission.charon_id')
            ->join('charon_defense_lab', 'charon_defense_lab.id', 'charon_defenders.defense_lab_id')
            ->join('user', 'charon_defenders.teacher_id', 'user.id')
            ->join('charon_lab', 'charon_lab.id', 'charon_defense_lab.lab_id')
            ->where('charon.course', $courseId)
            ->select('charon_defenders.id', 'charon_defenders.choosen_time', 'charon_defenders.student_id',
                'charon_defenders.student_name', 'charon_submission.charon_id', 'charon.defense_duration',
                'charon_defenders.my_teacher', 'charon_defenders.submission_id', 'charon_defenders.progress',
                'charon_defense_lab.id as charon_defense_lab_id', 'charon_defenders.teacher_id',
                'user.firstname', 'user.lastname', 'charon_lab.name as lab_name'
            )->orderBy('charon_defenders.choosen_time');

        if ($after != 'null' && $before != 'null') {
            $query->whereRaw('choosen_time BETWEEN ? AND ?', [
                Carbon::parse($after)->format('Y-m-d H:i:s'),
                Carbon::parse($before)->format('Y-m-d H:i:s')
            ]);
        } elseif ($after != 'null') {
            $query->whereRaw('choosen_time >= ?', [
                Carbon::parse($after)->format('Y-m-d H:i:s'),
            ]);
        } elseif ($before != 'null') {
            $query->whereRaw('choosen_time <= ?', [
                Carbon::parse($before)->format('Y-m-d H:i:s')
            ]);
        }
        if ($teacher_id != -1) {
            $query->whereRaw('teacher_id LIKE ?', [$teacher_id]);
        }
        if ($progress != 'null') {
            $query->whereRaw('progress LIKE ?', [$progress]);
        }

        $defenseRegistrations = $query->get();

        return $this->moveTeacher($defenseRegistrations);
    }

    /**
     * @version Registration 1.*
     *
     * @param $defenseRegistrations
     *
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
     *
     * @version Registration 1.*
     *
     * @param $defenseId
     * @param $newProgress
     * @param $newTeacherId
     *
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

    /**
     * @version Registration 1.*
     *
     * @param $studentId
     * @param $defenseLabId
     * @param $submissionId
     *
     * @return mixed
     */
    public function deleteRegistration($studentId, $defenseLabId, $submissionId)
    {
        return DB::table('charon_defenders')
            ->where('student_id', $studentId)
            ->where('defense_lab_id', $defenseLabId)
            ->where('submission_id', $submissionId)
            ->delete();
    }

    /**
     * @version Registration 1.*
     *
     * @param $studentId
     *
     * @return mixed
     */
    public function getStudentRegistrations($studentId)
    {
        return DB::table('charon_defenders')
            ->where('charon_defenders.student_id', $studentId)
            ->join('charon', 'charon.id', '=', 'charon_defenders.charon_id')
            ->join('user', 'charon_defenders.teacher_id', 'user.id')
            ->join('charon_defense_lab', 'charon_defenders.defense_lab_id', 'charon_defense_lab.id')
            ->join('charon_lab_teacher', 'charon_lab_teacher.teacher_id', 'charon_defenders.teacher_id')
            ->join('charon_lab', 'charon_lab.id', 'charon_defense_lab.lab_id')
            ->select(DB::raw('CONCAT(firstname, " ", lastname) AS teacher'))
            ->addSelect('charon.name', 'charon_defenders.choosen_time', 'charon_defenders.teacher_id',
                'charon_defenders.submission_id', 'charon_defenders.defense_lab_id',
                'charon_lab_teacher.teacher_location', 'charon_lab_teacher.teacher_comment', 'charon_lab.name as lab_name')
            ->distinct()
            ->get();
    }
}
