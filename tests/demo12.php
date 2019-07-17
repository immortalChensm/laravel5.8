<?php
/**
 * Created by PhpStorm.
 * User: 1655664358@qq.com
 * Date: 2019/7/16
 * Time: 10:12
 */

require_once '../vendor/autoload.php';

$a = [
    'china'=>'北京',
    'japanese'=>[
        'city'=>'东京',
        'address'=>[
            'a'=>'a1',
            'b'=>'b1'
        ]
    ],
    'korean'=>'首尔'
];

//print_r(\Illuminate\Support\Arr::forget($a,'japanese.city'));
//print_r($a);

//japanese.city
function forget(&$array, $keys)
{
    $original = &$array;
    $keys = (array) $keys;
    if (count($keys) === 0) {
        return;
    }
    foreach ($keys as $key) {
        $parts = explode('.', $key);
        $array = &$original;
        while (count($parts) > 1) {

            $part = array_shift($parts);
            echo $part;
            if (isset($array[$part]) && is_array($array[$part])) {
                $array = &$array[$part];//数据更新
            } else {
               // continue 2;
            }
        }
        unset($array[array_shift($parts)]);
    }
}
//
//forget($a,'japanese.address.a');
//print_r($a);
//mt_srand();
//echo mt_rand().PHP_EOL;
//echo mt_rand().PHP_EOL;
//echo mt_rand().PHP_EOL;

//print_r(preg_split('/\s+as\s+/i', " name as bname"));
//echo basename(str_replace('\\', '/', "Illuminate\\Database\\Test"));
//echo \Illuminate\Support\Str::studly('china');

//$view = new \Illuminate\View\View();

//echo is_array($view);
//echo $view instanceof \Illuminate\Contracts\Support\Renderable;

foreach([1,2,3,4,5] as $k){
    foreach([1,2,3] as $kk){
        if ($kk==2){
            break;
        }else{
            echo $k;
        }
    }
}