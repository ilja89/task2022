<?php

namespace TTU\Charon\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TTU\Charon\Http\Controllers\Controller;
use TTU\Charon\Models\Charon;

class CharonChainController extends Controller
{
    //
    public function __construct(Request $request) {
        parent::__construct($request);
    }

    public function index(Charon $charon) {
        Log::info("CharonChainController@index");
        return "You sucseded at coming here, charon name: ".($charon->name);
    }
}
