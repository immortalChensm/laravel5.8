<?php
/**
 * Created by PhpStorm.
 * User: 1655664358@qq.com
 * Date: 2019/7/10
 * Time: 18:16
 */

$class = \Illuminate\Redis\Limiters\DurationLimiter::class;

$b = compact('class','shared');
print_r($b);