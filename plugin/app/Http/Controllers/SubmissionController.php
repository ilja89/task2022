<?php

namespace TTU\Charon\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Models\Defenders;
use TTU\Charon\Repositories\CharonDefenseLabRepository;
use TTU\Charon\Repositories\CharonRepository;
use TTU\Charon\Repositories\LabRepository;
use TTU\Charon\Repositories\LabTeacherRepository;
use Zeizig\Moodle\Models\User;

class SubmissionController extends Controller
{

    /** @var CharonRepository */
    protected $charon_repository;

    /** @var LabTeacherRepository */
    protected $lab_teacher_repository;

    /** @var LabRepository */
    protected $lab_repository;

    /** @var CharonDefenseLabRepository */
    protected $charon_defense_lab_repository;

    public function __construct(CharonRepository $charon_repository, LabTeacherRepository $lab_teacher_repository,
                                LabRepository $lab_repository, CharonDefenseLabRepository $charon_defense_lab_repository)
    {
        $this->charon_repository = $charon_repository;
        $this->lab_teacher_repository = $lab_teacher_repository;
        $this->lab_repository = $lab_repository;
        $this->charon_defense_lab_repository = $charon_defense_lab_repository;
    }

    public function insert(Request $request)
    {
        $student_id = $request->input('studentid');
        $submission_id = $request->input('submission_id');
        $teacher = $request->input('selected');
        $lab_id = $request->input('defense_lab_id');
        $charon_id = $request->input('charon_id');
        $student_time = $request->input('student_choosen_time');

        $lab = $this->charon_defense_lab_repository->getDefenseLabById($lab_id);
        $lab_start = $lab->start;
        $lab_end = $lab->end;

        $duration = $this->charon_repository->getCharonById($charon_id)->defense_duration;
        if ($duration == null || $duration <= 0) {
            return "invalid setup";
        }

        $delta = Carbon::createFromFormat('Y-m-d H:i', $student_time)->diffInSeconds(Carbon::createFromFormat('Y-m-d H:i:s', $lab_start)) / 60;
        if ($delta % $duration != 0) {
            return "invalid chosen time";
        }

        $defense_count_student = $this->getUserPendingRegistrationsCount($student_id, $charon_id, $lab_start, $lab_end);
        $teacher_count = $this->getTeacherCount($charon_id, $lab_id);
        $count_for_current_time = $this->getRowCountForCurrentTime($student_time, $charon_id);

        if ($defense_count_student == 0) {
            if ($teacher == 1) {
                $course_id = $this->charon_repository->getCharonById($charon_id)->course;
                $student_teacher = $this->getTeacherForStudent($student_id, $course_id);
                $teacher_id = $student_teacher->id;
                if (count($this->getDefensesCountForTimeMyTeacher($student_time, $teacher_id, $lab_start, $lab_end)) > 0) return 'teacher is busy';
            } else {
                $teacher_id = $this->getTeachersByCharonAndLab($charon_id, $lab_id, $student_time);
            }
        } else {
            return 'user in db';
        }

        if ($count_for_current_time < $teacher_count) {
            $firstname = User::where('id', '=', $student_id)->get()[0]['firstname'];
            $lastname = User::where('id', '=', $student_id)->get()[0]['lastname'];
            $fullname = "$firstname $lastname";

            $defenders = new Defenders;
            $defenders->student_name = $fullname;
            $defenders->charon_id = $charon_id;
            $defenders->student_id = $student_id;
            $defenders->submission_id = $submission_id;
            $defenders->choosen_time = $student_time;
            $defenders->my_teacher = $teacher;
            $defenders->teacher_id = $teacher_id;
            $defenders->defense_lab_id = $lab_id;
            $defenders->progress = 'Waiting';
            $defenders->save();
            return 'inserted';
        } else return 'deleted';
    }

    public function getUserPendingRegistrationsCount($student_id, $charon_id, $lab_start, $lab_end)
    {
        return \DB::table('charon_defenders')
            ->join('charon_defense_lab', 'charon_defense_lab.id', 'charon_defenders.defense_lab_id')
            ->join('charon_lab', 'charon_defense_lab.lab_id', 'charon_lab.id')
            ->where('charon_defense_lab.charon_id', $charon_id)
            ->where('charon_defenders.student_id', $student_id)
            ->where('charon_defenders.progress', '!=', 'Done')
            ->whereBetween('charon_defenders.choosen_time', [date($lab_start), date($lab_end)])
            ->select('charon_lab.id')
            ->count();
    }

    public function getTeacherCount($charon_id, $lab_id)
    {
        return sizeof($this->lab_teacher_repository->getTeachersByCharonAndLabId($charon_id, $lab_id));
    }

    public function getTeachersByCharonAndLab($charon_id, $lab_id, $student_time)
    {
        $teachers_for_charon = $this->lab_teacher_repository->getTeachersByCharonAndLabId($charon_id, $lab_id);
        $array = array_values($teachers_for_charon->pluck('id')->toArray());

        $busy_teachers = array_values(\DB::table('charon_defenders')
            ->join('user', 'charon_defenders.teacher_id', 'user.id')
            ->select('user.id', 'user.firstname', 'user.lastname')
            ->where('charon_defenders.choosen_time', $student_time)
            ->where('charon_defenders.charon_id', $charon_id)
            ->whereIn('charon_defenders.teacher_id', $array)
            ->pluck('user.id')->toArray());

        $free_teachers = array_diff($array, $busy_teachers);
        $randrom_teacher_index = array_rand($free_teachers);
        return $free_teachers[$randrom_teacher_index];
    }


    public function getDefensesCountForTimeMyTeacher($time, $student_teacher_id, $start, $end)
    {

        return array_values(\DB::table('charon_defenders')
            ->select(DB::raw('SUBSTRING(choosen_time, 12, 5) as choosen_time'))
            ->where('choosen_time', 'like', '%' . $time . '%')
            ->where('teacher_id', $student_teacher_id)
            ->whereBetween('choosen_time', [date($start), date($end)])
            ->groupBy('choosen_time')
            ->having(DB::raw('count(*)'), '=', 1)
            ->pluck('choosen_time')
            ->toArray());
    }

    public function getDefensesCountForAllTeachers($time, $teacher_count, $start, $end)
    {

        return array_values(DB::table('charon_defenders')
            ->select(DB::raw('SUBSTRING(choosen_time, 12, 5) as choosen_time'))
            ->where('choosen_time', 'like', '%' . $time . '%')
            ->whereBetween('choosen_time', [date($start), date($end)])
            ->groupBy('choosen_time')
            ->having(DB::raw('count(*)'), '=', $teacher_count)
            ->pluck('choosen_time')
            ->toArray());
    }

    public function getTeacherForStudent($student_id, $course_id)
    {
        return $this->lab_teacher_repository->getTeacherForStudent($student_id, $course_id);
    }

    public function getRowCountForCurrentTime($student_time, $charon_id)
    {
        return \DB::table('charon_defenders')
            ->where('choosen_time', $student_time)
            ->where('charon_id', $charon_id)
            ->count();
    }

    public function getRowCountForPractise(Request $request)
    {
        $time = $request->input('time');
        $lab_id = $request->input('lab_id');
        $charon_id = $request->input('charon_id');
        $teacher_count = $this->getTeacherCount($charon_id, $lab_id);
        $student_id = $request->input('student_id');
        $choose_my_teacher = $request->input('my_teacher');
        $course_id = $this->charon_repository->getCharonById($charon_id)->course;

        $lab = $this->charon_defense_lab_repository->getDefenseLabById($lab_id);
        $start = $lab->start;
        $end = $lab->end;

        if ($choose_my_teacher == "true") {

            $student_teacher = $this->lab_teacher_repository->getTeacherForStudent($student_id, $course_id)->id;
            return $this->getDefensesCountForTimeMyTeacher($time, $student_teacher, $start, $end);
        } else {

            return $this->getDefensesCountForAllTeachers($time, $teacher_count, $start, $end);
        }
    }
}
