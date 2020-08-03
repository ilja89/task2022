<?php

namespace TTU\Charon\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use TTU\Charon\Events\CharonCreated;
use TTU\Charon\Models\Lab;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Zeizig\Moodle\Models\Course;

class LabsController extends Controller {

    public function insert(Request $request) {
        $lab = new Lab;
        $lab->start = $request->input('start')['time'];
        $lab->end = $request->input('end')['time'];
        $lab->save();

    }

    /**
     * Find all Labs in charon with given id.
     * grademaps with grade items.
     *
     * @param  integer $charonId
     *
     * @return Lab[]
     */

    public function findLabsByCharon(Request $request)
    {
        $charonId = $request->input('id');
        $labs = \DB::table('lab')  // id, start, end
        ->join('charon_defense_lab', 'charon_defense_lab.lab_id', 'lab.id') // id, lab_id, charon_id
        ->where('charon_id', $charonId)
            ->select('lab.id', 'start', 'end', 'course_id')
            ->get();
        return $labs;
    }

}