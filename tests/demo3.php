<?php
/**
 * Created by PhpStorm.
 * User: 1655664358@qq.com
 * Date: 2019/7/13
 * Time: 1:43
 */

$a = [1,2,3,4,5];
class B{
    function handle($stack,$val)
    {
        echo "b";
        return $stack($val);

    }
}
class C{
    function handle($stack,$val)
    {
        //return 'C';
        echo "c";
        return $stack($val);
    }
}
class A{
    public $a = [B::class,C::class];
    public function test()
    {
        return function ($stack,$val){
            return function ($b)use($val,$stack){
                //return $val*10;
                $obj = new $val;
                return $obj->{'handle'}(...[$stack,$b]);
            };
        };
    }

    function c()
    {
        return function (){
            return 10;
        };
    }

    function then()
    {
        $d = array_reduce($this->a,$this->test(),$this->c());
        print_r($d(1));
    }
}


(new A())->then();

