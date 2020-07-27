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
}