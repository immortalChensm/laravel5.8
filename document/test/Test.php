<?php
/**
 * Created by PhpStorm.
 * User: 1655664358@qq.com
 * Date: 2019/7/10
 * Time: 16:24
 */

//require_once '../../vendor/autoload.php';
//
//$event = new Composer\Script\Event();
//var_dump($event);
namespace T;
use Composer\Script\Event;
class Test
{
    public static function show(Event $event)
    {
        var_dump($event);
        return 1;
    }
}