<?php


namespace TTU\Charon\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Models\Charon;

class CharonController extends Controller
{
    public function get(Request $request)
    {
        $id = $request->input('id');
        return Charon::where('id', '=', $id)->get()[0]['defense_deadline'];
    }

    public function getAll(Request $request)
    {
        $id = $request->input('id');
        return Charon::where('id', '=', $id)->get()[0];
    }

    public function getDefenders(Request $request) {
        $student_id = $request->input('studentid');
        return \DB::table('charon_defenders')
            ->join('charon', 'charon.id', '=', 'charon_defenders.charon_id')
            ->join('user', 'charon_defenders.teacher_id','user.id')
            ->select(DB::raw('CONCAT(firstname, " ", lastname) AS teacher'))
            ->addSelect('charon.name', 'charon_defenders.choosen_time', 'charon_defenders.teacher_id', 'charon_defenders.submission_id', 'charon_defenders.defense_lab_id')
            ->where('charon_defenders.student_id', $student_id)
            ->get();

    }
}