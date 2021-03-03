<?php

namespace TTU\Charon\Http\Controllers;

class StaticPagesController extends Controller
{
    public function apiDocumentation()
    {
        return view('documentation.api_documentation');
    }
}
