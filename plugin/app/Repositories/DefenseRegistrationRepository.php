<?php

namespace TTU\Charon\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
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
     * Find all defence registration of a lab got with given identifier.
     *
     * @param int $labId
     *
     * @return Collection|Registration[]
     */
    public function getDefenceRegistrationsByLabId(int $labId): array
    {
        return DB::table('charon_defenders')
            ->join('charon_submission', 'charon_submission.id', 'charon_defenders.submission_id')
            ->join('charon', 'charon.id', 'charon_submission.charon_id')
            ->join('charon_defense_lab', 'charon_defense_lab.id', 'charon_defenders.defense_lab_id')
            ->join('charon_lab', 'charon_lab.id', 'charon_defense_lab.lab_id')
            ->where('charon_lab.id', $labId)
            ->select('charon.defense_duration')
            ->get()
            ->all();
    }

    /**
     * Get user registrations for charon
     *
     * @param int $userId
     * @param int $charonId
     * @return array
     */
    public function getUserRegistrations(int $userId, int $charonId): array
    {
        return DB::table('charon_defenders')
            ->join('charon_submission_user', 'charon_submission_user.submission_id', 'charon_defenders.submission_id')
            ->where('charon_submission_user.user_id', $userId)
            ->join('charon_defense_lab', 'charon_defense_lab.id', 'charon_defenders.defense_lab_id')
            ->join('charon_lab', 'charon_defense_lab.lab_id', 'charon_lab.id')
            ->where('charon_defenders.charon_id', $charonId)
            ->select('charon_defenders.id', 'charon_lab.end as lab_end', 'charon_defenders.progress')
            ->get()
            ->all();
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
            ->leftJoin('user', 'charon_defenders.teacher_id', 'user.id')
            ->join('charon_lab', 'charon_lab.id', 'charon_defense_lab.lab_id')
            ->where('charon.course', $courseId)
            ->select(
                'charon_defenders.id', 'charon_defenders.student_id',
                'charon_defenders.student_name', 'charon_submission.charon_id', 'charon.defense_duration',
                'charon_defenders.my_teacher', 'charon_defenders.submission_id', 'charon_defenders.progress',
                'charon_defense_lab.id as charon_defense_lab_id', 'charon_defenders.teacher_id',
                'user.firstname', 'user.lastname', 'charon_lab.name as lab_name', 'charon_lab.type'
            )->orderBy('charon_lab.id')
            ->orderBy('charon_defenders.defense_start')
            ->orderBy('charon_defenders.id')
            ->get();

        return $this->moveTeacher($defenseRegistrations);
    }

    /**
     * Get defense registrations by course. The needed is only $courseId, other parameters are used only for filtering.
     *
     * @param $courseId
     * @param $after - is used to get registrations where lab is active after this time
     * @param $before - is used to get registrations where lab is active before this time
     * @param $teacherId
     * @param $progress - status of the registration - 'Waiting', 'Defending' or 'Done'
     * @param bool $sessionStarted - is used to filter out others teachers' registrations to get only free
     * registrations and registration by $teacherId, if $sessionStarted parameter is true.
     * @return Collection|Registration[]
     */
    public function getDefenseRegistrationsByCourseFiltered($courseId, $after, $before, $teacherId, $progress, bool $sessionStarted)
    {
        /** @var \Illuminate\Database\Query\Builder $query */
        $query = DB::table('charon_defenders')
            ->join('charon_submission', 'charon_submission.id', 'charon_defenders.submission_id')
            ->join('charon', 'charon.id', 'charon_submission.charon_id')
            ->join('charon_defense_lab', 'charon_defense_lab.id', 'charon_defenders.defense_lab_id')
            ->leftJoin('user', 'charon_defenders.teacher_id', 'user.id')
            ->join('charon_lab', 'charon_lab.id', 'charon_defense_lab.lab_id')
            ->where('charon.course', $courseId)
            ->select('charon_defenders.id', 'charon_defenders.student_id',
                'charon_defenders.student_name', 'charon_submission.charon_id', 'charon.defense_duration',
                'charon_defenders.my_teacher', 'charon_defenders.submission_id', 'charon_defenders.progress',
                'charon_defense_lab.id as charon_defense_lab_id', 'charon_defenders.teacher_id',
                'user.firstname', 'user.lastname', 'charon_lab.name as lab_name', 'charon_lab.id as lab_id',
                'charon_lab.type')
            ->orderBy('lab_id')
            ->orderBy('charon_defenders.defense_start')
            ->orderBy('charon_defenders.id');

        if (!is_null($after) && !is_null($before)) {
            $query->whereRaw('end >= ? AND start <= ?', [
                Carbon::parse($after)->format('Y-m-d H:i:s'),
                Carbon::parse($before)->format('Y-m-d H:i:s')
            ]);
        } elseif (!is_null($after)) {
            $query->whereRaw('end >= ?', [
                Carbon::parse($after)->format('Y-m-d H:i:s'),
            ]);
        } elseif (!is_null($before)) {
            $query->whereRaw('start <= ?', [
                Carbon::parse($before)->format('Y-m-d H:i:s')
            ]);
        }

        if (!is_null($teacherId) && $sessionStarted) {
            $query->whereRaw('teacher_id = ?', [$teacherId])->orWhereNull('teacher_id');
        } else if (!is_null($teacherId)) {
            $query->whereRaw('teacher_id = ?', [$teacherId]);
        }
        if (!is_null($progress)) {
            $query->whereRaw('progress = ?', [$progress]);
        }

        $defenseRegistrations = $query->get();

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
     * @param $defenseStart
     * @return Registration
     */
    public function updateRegistration($defenseId, $newProgress, $newTeacherId, $defenseStart)
    {
        $defense = Registration::find($defenseId);
        $defense->progress = $newProgress;
        $defense->teacher_id = $newTeacherId;
        if (!is_null($defenseStart)) {
            $defense->defense_start = $defenseStart;
        }
        $defense->update();
        return $defense;
    }

    public function deleteRegistration($studentId, $defenseLabId, $submissionId): int
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
            ->join('charon_submission_user', 'charon_submission_user.submission_id', 'charon_defenders.submission_id')
            ->where('charon_submission_user.user_id', $studentId)
            ->join('charon', 'charon.id', '=', 'charon_defenders.charon_id')
            ->join('charon_defense_lab', 'charon_defenders.defense_lab_id', 'charon_defense_lab.id')
            ->join('charon_lab', 'charon_lab.id', 'charon_defense_lab.lab_id')
            ->select('charon.name', 'charon_lab.start as lab_start', 'charon_lab.end as lab_end',
                'charon_defenders.teacher_id', 'charon_defenders.submission_id', 'charon_defenders.defense_lab_id',
                'charon_lab.name as lab_name', 'charon_defenders.progress', 'charon_lab.type')
            ->distinct()
            ->get();
    }

    /**
     * Returns all lab registrations. If given progresses, then returns registrations only
     * where progress is in progresses list.
     *
     * @param int $labId
     * @param string[] $progresses
     *
     * @return array
     */
    public function getLabRegistrationsByLabId(int $labId, array $progresses = ['Waiting', 'Defending', 'Done']): array
    {
        return DB::table('charon_defenders')
            ->join("charon", "charon.id", "charon_defenders.charon_id")
            ->join("charon_defense_lab","charon_defense_lab.id","charon_defenders.defense_lab_id")
            ->where("charon_defense_lab.lab_id", $labId)
            ->whereIn("charon_defenders.progress", $progresses)
            ->select(
                "charon.name as charon_name",
                "charon.defense_duration",
                "charon_defenders.student_id",
                "charon_defenders.defense_start",
                "charon_defenders.progress"
            )
            ->orderBy("charon_defenders.defense_start")
            ->orderBy('charon_defenders.id')
            ->get()
            ->all();
    }

    /**
     * Find the total count of unfinished defences by lab id.
     *
     * @param int $labId
     *
     * @return int
     */
    public function countUndoneDefendersByLab(int $labId): int
    {
        return DB::table('charon_defenders')
            ->join('charon_defense_lab', 'charon_defense_lab.id', 'charon_defenders.defense_lab_id')
            ->where('charon_defense_lab.lab_id', $labId)
            ->where('charon_defenders.progress', '!=', 'Done')
            ->count();
    }

    /**
     * @param int $labId
     * @return Collection
     */
    public function getTeacherAndDefendingCharonByLab(int $labId): Collection
    {
        return DB::table('charon_defenders')
            ->join('charon_defense_lab', 'charon_defense_lab.id', 'charon_defenders.defense_lab_id')
            ->join('charon', 'charon.id', 'charon_defenders.charon_id')
            ->where('charon_defense_lab.lab_id', $labId)
            ->where('progress', 'Defending')
            ->whereNotNull('charon_defenders.teacher_id')
            ->select(
                'charon_defenders.teacher_id',
                'charon.name as charon',
                'charon_defenders.defense_start',
                'charon.defense_duration'
            )
            ->groupBy('teacher_id', 'charon', 'defense_start', 'defense_duration')
            ->get();
    }

    /**
     * Remove teachers from registration teachers which have progress waiting or defending.
     * If progress is defending, then change it to waiting.
     *
     * @param $labId
     * @param $teachersToRemove
     * @return int
     */
    public function removeTeachersFromUndoneRegistrations($labId, $teachersToRemove): int
    {
        return DB::table('charon_defenders')
            ->join('charon_defense_lab', 'charon_defense_lab.id', 'charon_defenders.defense_lab_id')
            ->where('charon_defense_lab.lab_id', $labId)
            ->where('charon_defenders.progress', '!=', 'Done')
            ->whereIn('charon_defenders.teacher_id', $teachersToRemove)
            ->update(array('charon_defenders.teacher_id' => null, 'charon_defenders.progress' => 'Waiting'));
    }

    /**
     * Return teacher' registration with progress 'Defending'.
     *
     * @param $labId
     * @param $teacherId
     * @return JsonResponse|null
     */
    public function getLabTeacherActiveRegistration($labId, $teacherId): ?JsonResponse
    {
        $registration = DB::table('charon_defenders')
            ->join('charon', 'charon.id', '=', 'charon_defenders.charon_id')
            ->join('charon_defense_lab', 'charon_defenders.defense_lab_id', 'charon_defense_lab.id')
            ->join('user', 'user.id', 'charon_defenders.student_id')
            ->where('charon_defenders.teacher_id', $teacherId)
            ->where('charon_defenders.progress', 'Defending')
            ->where('charon_defense_lab.lab_id', $labId)
            ->first(['charon_defenders.id','charon.name', 'charon_defenders.progress', 'user.firstname',
                'user.lastname', 'charon_defense_lab.lab_id', 'charon_defenders.teacher_id']);
        return $registration ? response()->json($registration): null;
    }

    /**
     * Updates only defending registrations to block having multiple defending registrations.
     *
     * @param int $labId
     * @param int $teacherId
     * @param string $progress
     * @return int
     */
    public function updateAllRegistrationsProgressByTeacherAndLab(int $labId, int $teacherId, string $progress): int
    {
        return DB::table('charon_defenders')
            ->join('charon_defense_lab', 'charon_defenders.defense_lab_id', 'charon_defense_lab.id')
            ->join('user', 'user.id', 'charon_defenders.student_id')
            ->where('charon_defenders.teacher_id', $teacherId)
            ->where('charon_defenders.progress', 'Defending')
            ->where('charon_defense_lab.lab_id', $labId)
            ->update(['charon_defenders.progress' => $progress]);
    }

    /**
     * Save defending progress.
     * @param $defenseId
     * @param $teacherId
     * @param $newProgress
     * @return Registration
     */
    public function updateRegistrationProgress($defenseId, $teacherId, $newProgress): Registration
    {
        $defense = Registration::find($defenseId);
        $defense->teacher_id = $teacherId;
        $defense->progress = $newProgress;
        $defense->update();
        return $defense;
    }

    public function getRegistrationOwner($studentId, $defenseLabId, $submissionId)
    {
        return DB::table('charon_defenders')
            ->join('charon_submission_user', 'charon_submission_user.submission_id', 'charon_defenders.submission_id')
            ->where('charon_submission_user.user_id', $studentId)
            ->where('charon_defenders.defense_lab_id', $defenseLabId)
            ->where('charon_defenders.submission_id', $submissionId)
            ->select('charon_defenders.student_id')
            ->first();
    }

}
