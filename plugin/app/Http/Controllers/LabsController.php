<?php

namespace TTU\Charon\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use TTU\Charon\Models\Lab;

class LabsController extends Controller {

    public function insert(Request $request) {
        $lab = new Lab;
        $lab->start = $request->input('start')['time'];
        $lab->end = $request->input('end')['time'];
        $lab->save();

    }

    public function findLabsByCharon(Request $request)
    {
        $charonId = $request->input('id');
        $labs = \DB::table('charon_lab')  // id, start, end
        ->join('charon_defense_lab', 'charon_defense_lab.lab_id', 'charon_lab.id') // id, lab_id, charon_id
        ->where('charon_id', $charonId)
            ->select('charon_defense_lab.id', 'start', 'end', 'course_id')
            ->get();
        return $labs;
    }

    public function findLabsByCharonLaterEqualToday(Request $request) {
        $charonId = $request->input('id');
        return \DB::table('charon_lab')  // id, start, end
        ->join('charon_defense_lab', 'charon_defense_lab.lab_id', 'charon_lab.id') // id, lab_id, charon_id
        ->where('charon_id', $charonId)
            ->whereDate('charon_lab.start', '>=', Carbon::now())
            ->select('charon_defense_lab.id', 'start', 'end', 'course_id')
            ->get();
    }
}
