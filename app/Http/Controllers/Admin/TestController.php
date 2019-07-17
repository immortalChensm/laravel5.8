<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\validateTestPost;
use App\Http\Controllers\Controller;

class TestController extends Controller
{
    //validateTestPost $validateTestPost
    function index()
    {

        //$data = $validateTestPost->all();
        return response()->json(['a']);

    }
}
