<?php
/**
 * Created by PhpStorm.
 * User: 1655664358@qq.com
 * Date: 2019/7/13
 * Time: 1:22
 */
class AV{
    public function handle($passable,$next)
    {
        echo "先看片".PHP_EOL;
        return $next($passable);
        //return true;
        //throw new RuntimeException("error");
    }
}
class Sleeping{
    public function handle($passable,$next)
    {
        echo "再睡觉".PHP_EOL;
        return $next($passable);
    }
}
class pipeline{

    public $pipes = [AV::class,Sleeping::class];
    public $passable = ['get'=>['a','c','b'],'post'=>['name'=>'jack']];
    public function callbacks()
    {
        return function ($passable){
            echo "然后XXOO".PHP_EOL;
        };
    }
    public function prepareDestination(Closure $destination)
    {
        return function ($passable)use($destination){
            return $destination($passable);
        };
    }
    public function then(Closure $cbk)
    {

        $pipeline = array_reduce(
            array_reverse($this->pipes), $this->carry(), $this->prepareDestination($cbk)
        );

        return $pipeline($this->passable);
    }

    public function carry()
    {

        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                if (is_callable($pipe)) {
                    return $pipe($passable, $stack);
                } elseif (! is_object($pipe)) {
                    $pipe = new $pipe;
                    $parameters = [];
                    $parameters = array_merge([$passable, $stack], $parameters);
                } else {
                    $parameters = [$passable, $stack];
                }
                $response = method_exists($pipe, 'handle')
                    ? $pipe->{'handle'}(...$parameters)
                    : $pipe(...$parameters);

                return $response;
            };
        };
    }
}
class start{

    public $middleware = [];
    public function __construct()
    {

        $pipe = new pipeline();
        return $pipe->then($pipe->callbacks());
    }
}

(new start());
