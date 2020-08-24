<?php


namespace TTU\Charon\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use TTU\Charon\Models\Charon;
use TTU\Charon\Repositories\LabTeacherRepository;

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
        $lab_repository = new LabTeacherRepository();

        $student_teacher = $lab_repository->getTeacherForStudent($student_id);
        $student_teacher_data = json_decode($student_teacher, true);
        $firstname = $student_teacher_data[0]['firstname'];
        $lastname = $student_teacher_data[0]['lastname'];
        $teacher_fullname = "$firstname $lastname";

        $teacher_field = "teacher";

        $arary =  DB::table('defenders')
            ->join('charon', 'charon.id', '=', 'defenders.charon_id')
            ->where('student_id', '=', $student_id)
            ->select('charon.name', 'defenders.choosen_time', 'defenders.teacher_id', 'defenders.submission_id', 'defenders.defense_lab_id')
            ->get();

        for ($i = 0; $i < sizeof($arary); $i++) {
            if ($arary[$i]->teacher_id == -1) $arary[$i]->$teacher_field = "Another teacher";
            else $arary[$i]->$teacher_field = $teacher_fullname;
        }
        return $arary;

    }

}
