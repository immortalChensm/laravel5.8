<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\validateTestPost;
use App\Http\Controllers\Controller;
use App\Jobs\Test;
use App\User;
use Illuminate\Cache\FileStore;
use Illuminate\Cache\RedisStore;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Redis\RedisManager;
use Illuminate\Session\FileSessionHandler;
use Illuminate\Session\SessionManager;
use Illuminate\Session\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\MessageBag;

class TestController extends Controller
{
//alidateTestPost $validateTestPost
    function index()
    {

        //$data = $validateTestPost->all();
        /** @var \Illuminate\Validation\Factory $factory */
//        $factory = app(Factory::class);
//        $validator = $factory->make(['name'=>'jack'],['name'=>'required|email'],['name.required'=>'填写东西啊','name.email'=>'填写的不是邮箱啊']);
//        if ($validator->fails()){
//            /** @var MessageBag $message */
//            $message = $validator->errors()->getMessages();
//            print_r($message);
//        }else{
//            echo 'ok';
//        }
//        Cache::put("name","laravel",10);
//        Cache::put("address","china");
//        Cache::put("city","ssss");
//        Cache::put("age",200,time()+3600);
//        $data = Cache::get("name");
//        echo $data;
        //Cache::forever("girl","lucy");
        //echo Cache::pull("girl","test");
        //echo \cache()['name'];

        //$file = new FileStore(new Filesystem(),storage_path("framework/cache/data"));
        //$repository = new Repository($file);
        //echo $repository->set("name","bbb");
        //echo $repository->get("name");
        //echo $repository['name'];
        //$repository['name'] = 'php是最吊的语言';

        //echo $file->get("name");
        //Cache::put("car","boma");
        //echo Cache::get("car");
        /** @var RedisManager $redisManager */
//        $redisManager = app("redis");
//        $redis = new RedisStore($redisManager,"cache","cache");
//        echo $redis->put("car","aodi",50);
//        //echo $redis->get("car");
//
//        //$redisClient = $redisManager->connections("cache");
//        echo $redisManager->get("cache.car");

        //Test::dispatch();
        //Test::dispatchNow();
        //$user = new User();
        //$user->name = "jacl";
        //$user->save();

        //print_r(app('events')->dispatch("装逼",request()));
        /**
         * @var SessionManager $session
         */
        $session = app('session');

        /**
         * @var Store $fileSessionHandler
         */
        $fileSessionHandler = $session->driver("file");

        $fileSessionHandler->put("nibi","nibi");
        echo $fileSessionHandler->get("nibi");

        //return response()->json(['a']);
        //return ['aaa'];
        $data = Collection::make(['aaa']);
        return view("admin.index", compact('data'));

    }
}
