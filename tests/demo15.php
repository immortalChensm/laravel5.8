<?php
/**
 * Created by PhpStorm.
 * User: 1655664358@qq.com
 * Date: 2019/7/22
 * Time: 15:10
 */

//$users = [
//
//    [
//        'userProfile'=>['userId'=>1,'name'=>'张三'],
//        'rightNode'=>[
//            [
//                'userId'=>3,
//                'name'=>'小花'
//            ]
//        ],
//        'leftNode'=>[
//            [
//                'userProfile'=>['userId'=>4,'name'=>'张四'],
//                'rightNode'=>[],
//                'leftNode'=>[]
//            ],
//            [
//                'userProfile'=>['userId'=>4,'name'=>'张五'],
//                'rightNode'=>[],
//                'leftNode'=>[]
//            ],
//        ],
//    ]
//];

$users = [

        'userProfile'=>['userId'=>1,'name'=>'张三'],
        'rightNode'=>[],
        'leftNode'=>[],
        'parent'=>0,

];

function preTraverse($tree){
        if ($tree){
            echo $tree['userProfile']['name'].PHP_EOL;
            //preTraverse($tree['rightNode']);
            //preTraverse($tree['leftNode']);
            if (isset($tree['rightNode'])){
                foreach ($tree['rightNode'] as $item){
                    preTraverse($item);
                }
            }

            if (isset($tree['leftNode'])){
                foreach ($tree['leftNode'] as $item){
                    preTraverse($item);
                }
            }


        }
}

function addTree(&$tree,$userId,$relationName,$whoId,$whoName)
{
        if ($tree){
            if ($tree['userProfile']['userId']==$userId) {
                switch ($relationName) {
                    case '儿子':
                        $node               = ['userId' => $whoId, 'name' => $whoName];
                        $tree['leftNode'][] = ['userProfile' => $node, 'leftNode' => [], 'rightNode' => []];
                        break;
                    case '孙子':
                        //检测孙子跟哪个儿子传联没有
                        $sonNode  = ['userId' => 0, 'name' => ''];
                        $tempNode = ['userProfile' => $sonNode, 'leftNode' => [], 'rightNode' => []];

                        $grandSonNode     = ['userId' => $whoId, 'name' => $whoName];
                        $grandSonNodeTemp = ['userProfile' => $grandSonNode, 'leftNode' => [], 'rightNode' => []];

                        $tempNode['leftNode'][] = $grandSonNodeTemp;
                        $tree['leftNode'][]     = $tempNode;
                        break;
                    case '老婆':
                        $node               = ['userId' => $whoId, 'name' => $whoName];
                        $tree['rightNode'][] = ['userProfile' => $node];
                        break;
                    case '爸爸':
                        $parentNode['userProfile']               = ['userId' => $whoId, 'name' => $whoName];
                        $parentNode['leftNode'][] = $tree;
                        $parentNode['rightNode'][] = [];

                        return $parentNode;
                        break;
                    case '爷爷':

                        break;
                }
            }

            if (isset($tree['rightNode'])){
                foreach ($tree['rightNode'] as $k=>$item){
                    addTree($tree['rightNode'][$k],$userId,$relationName,$whoId,$whoName);
                }
            }
            if (isset($tree['leftNode'])){
                foreach ($tree['leftNode'] as $k=>$item){
                    addTree($tree['leftNode'][$k],$userId,$relationName,$whoId,$whoName);
                }
            }



        }
}
//preTraverse($users);
//addTree($users,1,'孙子',3,'张四');//我和孙子
//addTree($users,1,'儿子',5,'张六');//我和儿子
//
//addTree($users,5,'儿子',3,'张四');//儿子和孙子
//print_r($users);
//preTraverse($users);
require_once '../vendor/autoload.php';
//echo \Illuminate\Support\Str::slug(env('APP_NAME', 'laravel'), '_').'_session';
//$id = "eyJpdiI6IjZPaHZnMldXdjhlNnE1QTg4c201ZXc9PSIsInZhbHVlIjoiMGZUT1FRSGdwOUdRcHgxYVhcL09VK0tMZVFKa21nVmR1RGpxWUdaVlpJdmRcL1l0ZkxlVVBaelFxWkpwV0FsSXcwIiwibWFjIjoiZTAwZjIzZjkyNzlmZDM2OTczYWY0Y2M1MWFhY2U2OWFhNjRhNGI4NWFkNzg4ZGM5YjY3NmUwZWE1NDE0ZjFlYiJ9";
//echo  is_string($id) && ctype_alnum($id) && strlen($id) === 40;

print_r(unserialize('a:4:{s:6:"_token";s:40:"6yi69oxoJyRifg1EXhqn11kDoVMkZeW7eTk0l0jD";s:4:"nibi";s:4:"nibi";s:9:"_previous";a:1:{s:3:"url";s:24:"http://laravel8.com/test";}s:6:"_flash";a:2:{s:3:"old";a:0:{}s:3:"new";a:0:{}}}'));
print_r(unserialize('a:3:{s:6:"_token";s:40:"LBs1QYQ6GDojXSY5gQR7S7B7dVZxjPgVTg6qJxgX";s:9:"_previous";a:1:{s:3:"url";s:24:"http://laravel8.com/test";}s:6:"_flash";a:2:{s:3:"old";a:0:{}s:3:"new";a:0:{}}}'));
