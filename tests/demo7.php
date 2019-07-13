<?php
/**
 * Created by PhpStorm.
 * User: 1655664358@qq.com
 * Date: 2019/7/13
 * Time: 15:14
 */

class Shooting {
    public static function handle(Closure $next) {
        echo '射击', '<br>';;
        $next();

    }
}
class Gun
{
    public static function handle(Closure $next) {
        echo '拿起武器', '<br>';
        $next();
    }
}


$firstSlice = function (){
    echo '打死这些傻逼～', '<br>';
};

$arr = [
    'Shooting',
    'Gun'
];
global $func;
function getSlice(){
    return function ($stack, $pipe){
        //echo $pipe;
        global $func;
        //$pipe第一次时是Shooting
        //$stack=打死这些傻逼～
        //但没有运行，直接返回
        /**
         * function () use ($stack=$firstSlice我打死这些傻逼～ , $pipe=Shooting){
        return $pipe::handle($stack);打死这些傻逼～
        };记为A
         */
        //第二次时$pipe=Gun
        //$stack=A
        /**
         *
         * function () use ($stack=A函数, $pipe=Gun){
        return $pipe::handle($stack=A函数);
        };
         */
        return $func=function () use ($stack, $pipe){
            return $pipe::handle($stack);
        };
    };
}

/**
 *
 * function ($stack, $pipe){
        return function () use ($stack, $pipe){
            return $pipe::handle($stack);
        };
    };
 */

$go = array_reduce($arr, getSlice(), $firstSlice);
global $func;
echo $func==$go;
$go();