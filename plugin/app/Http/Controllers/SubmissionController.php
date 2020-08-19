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
        $course_id = $request->input('course_id');

        $teacher_fullname = 'Another teacher';
        $defense_count_student = $this->getUserDefenseDate($student_id, $charon_id ,$lab_start, $lab_end);
        $teacher_count = $this->getTeacherCount($course_id);
        $count_for_current_time = $this->getRowCountForCurrentTime($student_time);

        if ($defense_count_student == 0) {
            if ($teacher == 1) {
                $student_teacher = $this->getTeacheForStudent($student_id);
                $result = json_decode($student_teacher, true);
                $teacher_firstname = $result[0]['firstname'];
                $teacher_lastname = $result[0]['lastname'];
                $teacher_fullname = "$teacher_firstname $teacher_lastname";
                if ($this->getDefensesCountForTimeMyTeacher($student_time, $teacher_fullname) > 0) return 'teacher is busy';
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
            $defenders->teacher_name = $teacher_fullname;
            $defenders->defense_lab_id = $lab_id;
            $defenders->save();
            if ($this->getRowCountForCurrentTime($student_time) == $teacher_count) return 'delete, inserted';
            return 'inserted';
        } else return 'deleted';
    }

    public function getUserDefenseDate($student_id, $charon_id, $lab_start, $lab_end)
    {
        return \DB::table('defenders')
            ->join('charon_defense_lab', 'charon_defense_lab.id', 'defenders.defense_lab_id')
            ->join('lab', 'charon_defense_lab.lab_id', 'lab.id')
            ->where('charon_defense_lab.charon_id', $charon_id)
            ->where('defenders.student_id', $student_id)
            ->whereBetween('defenders.choosen_time', [date($lab_start), date($lab_end)])
            ->select('lab.id', 'lab.start', 'lab.end', 'defenders.choosen_time')
            ->count();
    }

    public function getTeacherCount($course_id) {
        $lab_teacher_reposity = new LabTeacherRepository();
        return sizeof($lab_teacher_reposity->getTeachersByCourseId($course_id));
    }

    public function getDefensesCountForTimeMyTeacher($student_time, $student_teacher) {
        return \DB::table('defenders')->where('choosen_time', '=', $student_time)
            ->where('my_teacher', 1)
            ->where('teacher_name', '=', $student_teacher)->count();
    }

    public function getDefenseCountForTimeAnotherTecher($student_time) {
        return \DB::table('defenders')->where('choosen_time', '=', $student_time)
            ->where('my_teacher', 0)
            ->count();
    }

    public function getTeacheForStudent($student_id) {
        $labTeacherRepository = new LabTeacherRepository();
        return $labTeacherRepository->getTeacherForStudent($student_id);
    }

    public function getRowCountForCurrentTime($student_time) {
        return \DB::table('defenders')->where('choosen_time', $student_time)->count();
    }

    public function getRowCountForPractise(Request $request) {
        $time = $request->input('time');
        $course = $request->input('course');
        $teacher_count = $this->getTeacherCount($course);

        return \DB::table('defenders')
            ->select('choosen_time', DB::raw('count(*) as total'))
            ->where('choosen_time', 'like', '%' . $time . '%')
            ->groupBy('choosen_time')
            ->having('total', '=', $teacher_count)
            ->get();
    }
}

