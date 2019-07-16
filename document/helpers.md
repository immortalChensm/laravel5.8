### helpers   
value()函数【是匿名函数则支持返回|返回自己】
```php 
if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}
``` 
class_uses_recursive函数【获取某一个所有继承的类，包括父类，子类全部返回】
```php  
function class_uses_recursive($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];
        //本类+父类=构成新数组
        foreach (array_reverse(class_parents($class)) + [$class => $class] as $class) {
            $results += trait_uses_recursive($class);
        }
        //去除重复
        return array_unique($results);
    }
```  
trait_uses_recursive函数
```php  
function trait_uses_recursive($trait)
    {
    //得到这个类所继承的trait类【比如你看一下那User模型，继承了一堆】
        $traits = class_uses($trait);

    //循环递归，不断的获取子类所继承的trait类
        foreach ($traits as $trait) {
            $traits += trait_uses_recursive($trait);
        }

        return $traits;
    }
```  
class_basename【取得类名名称】如Illumiante\\Database\\Test则返回Test
```php  
function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
```