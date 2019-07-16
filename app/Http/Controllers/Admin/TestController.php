<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Routing\RouteCollection;

class TestController extends Controller
{
    //
    function index(Request $request,User $user)
    {
        $data = DB::table("test")->get();
        /**@var RouteCollection */
       //print_r(app('routes')->get(app('request')->getMethod()));
        /** @var Request */
        //print_r(app("request")->headers->get('user-agent'));
        /** @var DatabaseManager $db */
        $db = app("db");
        $obj = new \ReflectionClass($db);
        print_r($obj->getMethods());

        return view("admin.index",compact('data'));
    }
}
