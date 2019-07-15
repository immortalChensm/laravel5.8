<?php
/**
 * Created by PhpStorm.
 * User: 1655664358@qq.com
 * Date: 2019/7/15
 * Time: 16:05
 */
require_once '../vendor/autoload.php';

class A{
    public function matches($request,$including){
        echo "我是A".PHP_EOL;
       // return true;
    }
}
class B{
    public function matches($request,$including){
        echo "我是B".PHP_EOL;

        //return true;
    }
}
$routes = [
    'a'=>new A(),
    'b'=>new B()
];
$request=1;
$includingMethod=2;
//$route = collect($routes);
//
//$ret = $route->first(function ($value) use ($request, $includingMethod) {
//    return $value->matches($request, $includingMethod);
//});
//
//var_dump($ret);
//$ret->matches(1,2);

//foreach ($routes as $k=>$v){
//    echo $k.PHP_EOL;
//    //return true;
//    foreach(['a','b'] as $key=>$value){
//        if (!$k==$key){
//            return false;
//        }
//    }
//}

//$callback = function ($value) use ($request, $includingMethod) {
//
//   echo $value;
//    foreach(['a','b'] as $k=>$v){
//        if (!$v==$value){
//            return false;
//        }
//    }
//    return true;
//};
//
//
//function test($routes,$callback)
//{
//    foreach ($routes as $key => $value) {
//        if (call_user_func($callback, $key,$value )) {
//            return $key;
//        }
//    }
//}
//
//$test = test($routes,$callback);
//echo $test;

echo !is_null(null);