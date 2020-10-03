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

}