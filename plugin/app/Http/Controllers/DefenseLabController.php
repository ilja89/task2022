<?php


namespace TTU\Charon\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class DefenseLabController extends Controller
{
    public function deleteReg(Request $request)
    {
        $student_id = $request->input('student_id');
        $defense_lab_id = $request->input('defLab_id');
        $charon_id = $request->input('charon_id');

        return DB::table('charon_defenders')
            ->where('student_id', $student_id)
            ->where('defense_lab_id', $defense_lab_id)
            ->where('charon_id', $charon_id)
            ->delete();
    }

    public function getStudentGroup(Request $request) {
        $student_id = $request->input('studentid');
        return \DB::table('groups_members')
            ->where('userid', $student_id)
            ->count();
    }
}

