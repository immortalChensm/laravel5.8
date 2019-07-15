<?php
/**
 * Created by PhpStorm.
 * User: 1655664358@qq.com
 * Date: 2019/7/15
 * Time: 15:04
 */

//preg_match_all('/\{(\w+?)\?\}/', "http://www.baidu.com/posts/{post?}/comments/{comment?}", $matches);
//print_r(array_fill_keys($matches[1], null));
//
//echo preg_replace('/\{(\w+?)\?\}/', '{$1}', "http://www.baidu.com/posts/{post?}/comments/{comment?}");

$c = rawurldecode("/test");
echo preg_match("#^/test$#sDu", $c);