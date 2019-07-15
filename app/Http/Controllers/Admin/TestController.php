<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    //
    function index(Request $request,User $user)
    {
        $data = DB::table("test")->get();
        return view("admin.index",compact('data'));
    }
}
