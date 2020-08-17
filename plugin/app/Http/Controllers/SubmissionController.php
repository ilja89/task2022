<?php

namespace TTU\Charon\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use TTU\Charon\Models\Defenders;
use Zeizig\Moodle\Models\User;

class SubmissionController extends Controller {

    public function insert(Request $request) {
        $id = $request->input('studentid');
        $submission_id = $request->input('submission_id');
        $lab_start = $request->input('lab_start');
        $lab_end = $request->input('lab_end');
        $teacher = $request->input('selected');
        $lab_id = $request->input('defense_lab_id');
        $charon_id = $request->input('charon_id');
        $student_time = $request->input('student_choosen_time');

        $defense_count = $this->getUserDefenseDate($id, $charon_id, $lab_id, $lab_start, $lab_end);
        if ($defense_count == 0) {
            $firstname = User::where('id', '=', $id)->get()[0]['firstname'];
            $lastname = User::where('id', '=', $id)->get()[0]['lastname'];
            $fullname = "$firstname $lastname";

            $defenders = new Defenders;
            $defenders->student_name = $fullname;
            $defenders->charon_id = $charon_id;
            $defenders->student_id = $id;
            $defenders->submission_id = $submission_id;
            $defenders->choosen_time = $student_time;
            $defenders->my_teacher = $teacher;
            $defenders->defense_lab_id = $lab_id;
            $defenders->save();
            return 'inserted';
        } else {
            return 'fail';
        }
    }

    public function getUserDefenseDate($student_id, $charon_id, $lab_id, $lab_start, $lab_end) {

        $defense_data = \DB::table('defenders')
            ->join('charon_defense_lab', 'charon_defense_lab.id', 'defenders.defense_lab_id')
            ->join('lab', 'charon_defense_lab.lab_id', 'lab.id')
            ->where('charon_defense_lab.charon_id', $charon_id)
            ->where('defenders.student_id', $student_id)
            ->whereBetween('defenders.choosen_time', [date($lab_start), date($lab_end)])
            ->select('lab.id', 'lab.start', 'lab.end', 'defenders.choosen_time')
            ->count();

        return $defense_data;
    }
}
