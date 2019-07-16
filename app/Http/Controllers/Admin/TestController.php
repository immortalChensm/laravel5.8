<?php

namespace App\Http\Controllers\Admin;

use App\Test;
use App\User;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\Connectors\MySqlConnector;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\MySqlBuilder;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Routing\RouteCollection;

class TestController extends Controller
{
    //
    function index(Request $request)
    {
        $data = DB::table("test")->get();
//        /**@var RouteCollection */
//       //print_r(app('routes')->get(app('request')->getMethod()));
//        /** @var Request */
//        //print_r(app("request")->headers->get('user-agent'));
//        /** @var DatabaseManager $db */
//        $db = app("db");
//        $obj = new \ReflectionClass($db);
//        print_r($obj->getMethods());

//        /** @var Application $app */
//        $app = app();
//        /** @var DatabaseManager $db */
//        $db = $app['db'];
//        $data = $db->table("test")->get();

//        $dbFactory = new ConnectionFactory(app());
//        $dbManager = new DatabaseManager(app(),$dbFactory);
//        /** @var MySqlConnection $connection */
//        $connection = $dbManager->connection();
//        $data = $connection->table("test")->get("name");

        /** @var MySqlConnection $db */
//        $db = new MySqlConnection(function (){
//            return (new MySqlConnector())->connect(config("database")['connections']['mysql']);
//        },config("database")['connections']['mysql']['database'],"",config("database")['connections']['mysql']);
//
//        $data = $db->table("test")->get();
        /** @var DatabaseManager $db */
//        $db = app("db");
//        $connection = $db->connection();
//        $builder = new Builder($connection);
////        $builder->from("test")
////        ->where("id","<>",1)
////        ->whereIn("name",[1,2,3]);
////        $sql = $builder->toSql();
////        $data = $sql;
//        $builder->where("name","%laravel%")
//            ->max("age");
//
//        $data = $connection->select($builder->toSql());

        //$test = new Test(['age'=>100,'name'=>'jack']);
        //$data = $test->save();


        return view("admin.index",compact('data'));
    }
}
