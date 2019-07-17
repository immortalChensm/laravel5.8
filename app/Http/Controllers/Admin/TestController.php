<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\validateTestPost;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Support\MessageBag;

class TestController extends Controller
{
//alidateTestPost $validateTestPost
    function index()
    {

        //$data = $validateTestPost->all();
        /** @var \Illuminate\Validation\Factory $factory */
        $factory = app(Factory::class);
        $validator = $factory->make(['name'=>'jack'],['name'=>'required|email'],['name.required'=>'填写东西啊','name.email'=>'填写的不是邮箱啊']);
        if ($validator->fails()){
            /** @var MessageBag $message */
            $message = $validator->errors()->getMessages();
            print_r($message);
        }else{
            echo 'ok';
        }
        return response()->json(['a']);

    }
}
