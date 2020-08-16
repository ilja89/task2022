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
        $time = $request->input('time');
        $teacher = $request->input('selected');
        $lab_id = $request->input('defense_lab_id');

        $firstname = User::where('id', '=', $id)->get()[0]['firstname'];
        $lastname = User::where('id', '=', $id)->get()[0]['lastname'];
        $fullname = "$firstname $lastname";

        $defenders = new Defenders;
        $defenders->student_name = $fullname;
        $defenders->student_id = $id;
        $defenders->submission_id = $submission_id;
        $defenders->choosen_time = $time;
        $defenders->my_teacher = $teacher;
        $defenders->defense_lab_id = $lab_id;

        $defenders->save();
    }
}
