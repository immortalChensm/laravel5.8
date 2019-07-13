<?php
/**
 * Created by PhpStorm.
 * User: 1655664358@qq.com
 * Date: 2019/7/13
 * Time: 15:01
 */

function sum($carry, $item) {
    echo "carry=".$carry;
    echo ",item=".$item.PHP_EOL;
    $carry += $item;

    echo " return carray=".$carry.PHP_EOL;
    return $carry;
}

$a = array(1, 2, 3, 4, 5);

//var_dump(array_reduce($a, 'sum', 10));
$b = (array_reduce($a, 'sum', 10));
print_r($b);