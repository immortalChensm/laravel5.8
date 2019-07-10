<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    //
    function index()
    {
        return DB::table("test")->get();
    }
}
