<?php

namespace TTU\Charon\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Models\Defenders;
use TTU\Charon\Repositories\LabTeacherRepository;
use Zeizig\Moodle\Models\User;

class SubmissionController extends Controller
{

    public function insert(Request $request)
    {
        $student_id = $request->input('studentid');
        $submission_id = $request->input('submission_id');
        $lab_start = $request->input('lab_start');
        $lab_end = $request->input('lab_end');
        $teacher = $request->input('selected');
        $lab_id = $request->input('defense_lab_id');
        $charon_id = $request->input('charon_id');
        $student_time = $request->input('student_choosen_time');

        $defense_count_student = $this->getUserDefenseDataCount($student_id, $charon_id ,$lab_start, $lab_end);
        $teacher_count = $this->getTeacherCount($charon_id, $lab_id);
        $count_for_current_time = $this->getRowCountForCurrentTime($student_time, $charon_id);

        if ($defense_count_student == 0) {
            if ($teacher == 1) {
                $student_teacher = $this->getTeacheForStudent($student_id);
                $result = json_decode($student_teacher, true);
                $teacher_id = $result[0]['id'];
                if (count($this->getDefensesCountForTimeMyTeacher($student_time, $teacher_id, $charon_id, $lab_start, $lab_end)) > 0) return 'teacher is busy';
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

    public function getUserDefenseDataCount($student_id, $charon_id, $lab_start, $lab_end)
    {
        return \DB::table('charon_defenders')
            ->join('charon_defense_lab', 'charon_defense_lab.id', 'charon_defenders.defense_lab_id')
            ->join('charon_lab', 'charon_defense_lab.lab_id', 'charon_lab.id')
            ->where('charon_defense_lab.charon_id', $charon_id)
            ->where('charon_defenders.student_id', $student_id)
            ->whereBetween('charon_defenders.choosen_time', [date($lab_start), date($lab_end)])
            ->select('charon_lab.id', 'charon_lab.start', 'charon_lab.end', 'charon_defenders.choosen_time')
            ->count();
    }

    public function getTeacherCount($charon_id, $lab_id) {
        $lab_teacher_reposity = new LabTeacherRepository();
        return sizeof($lab_teacher_reposity->getTeachersByCharonAndLabId($charon_id, $lab_id));
    }

    public function getTeachersByCharonAndLab($charon_id, $lab_id, $student_time) {
        $lab_teacher_repository = new LabTeacherRepository();
        $teachers_for_charon = $lab_teacher_repository->getTeachersByCharonAndLabId($charon_id, $lab_id);
        $array = array_values($teachers_for_charon->pluck('id')->toArray());

        $busy_teachers = array_values(\DB::table('charon_defenders')
            ->join('user', 'charon_defenders.teacher_id', 'user.id')
            ->select('user.id','user.firstname', 'user.lastname')
            ->where('charon_defenders.choosen_time', $student_time)
            ->where('charon_defenders.charon_id', $charon_id)
            ->whereIn('charon_defenders.teacher_id', $array)
            ->pluck('user.id')->toArray());

        $free_teachers = array_diff($array, $busy_teachers);
        $randrom_teacher_index = array_rand($free_teachers);
        return $free_teachers[$randrom_teacher_index];
    }


    public function getDefensesCountForTimeMyTeacher($time, $student_teacher_id, $charon_id, $start, $end) {

        return array_values(\DB::table('charon_defenders')
            ->select(DB::raw('SUBSTRING(choosen_time, 12, 5) as choosen_time'))
            ->where('choosen_time', 'like', '%' . $time . '%')
            ->where('charon_id', $charon_id)
            ->where('teacher_id', $student_teacher_id)
            ->whereBetween('choosen_time', [date($start), date($end)])
            ->groupBy('choosen_time')
            ->having(DB::raw('count(*)'), '=', 1)
            ->pluck('choosen_time')
            ->toArray());
    }

    public function getTeacheForStudent($student_id) {
        $labTeacherRepository = new LabTeacherRepository();
        return $labTeacherRepository->getTeacherForStudent($student_id);
    }

    public function getRowCountForCurrentTime($student_time, $charon_id) {
        return \DB::table('charon_defenders')
            ->where('choosen_time', $student_time)
            ->where('charon_id', $charon_id)
            ->count();
    }

    public function getRowCountForPractise(Request $request) {
        $time = $request->input('time');
        $lab_id = $request->input('lab_id');
        $charon_id = $request->input('charon_id');
        $teacher_count = $this->getTeacherCount($charon_id, $lab_id);
        $start = $request->input('start');
        $end = $request->input('end');
        $student_id = $request->input('studentid');
        $lab_teacher_repository = new LabTeacherRepository();
        $teacher_is_busy = [];
        $student_group = $request->input('group');

        if ($student_group != 0) {
            $student_teacher = $lab_teacher_repository->getTeacherForStudent($student_id)->pluck('id')->toArray();
            if ($student_teacher[0] != 0) {
                $teacher_is_busy = $this->getDefensesCountForTimeMyTeacher($time, $student_teacher[0], $charon_id, $start, $end);
            }
        }

        $notavailable_time = array_values(DB::table('charon_defenders')
            ->select(DB::raw('SUBSTRING(choosen_time, 12, 5) as choosen_time'))
            ->where('choosen_time', 'like', '%' . $time . '%')
            ->where('charon_id', $charon_id)
            ->whereBetween('choosen_time', [date($start), date($end)])
            ->groupBy('choosen_time')
            ->having(DB::raw('count(*)'), '=', $teacher_count)
            ->pluck('choosen_time')
            ->toArray());

        if (count($teacher_is_busy) != 0 && count($notavailable_time) == 0) return $teacher_is_busy;
        return array(1, $notavailable_time);

    }
}
