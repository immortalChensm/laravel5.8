### Arr类   
get方法【$array不是数组的话，直接返回$default，$key未传直接返回$array,$key含有.符号直接返回$key索引的值或是默认值】    
```php  
public static function get($array, $key, $default = null)
    {
    //$array不是数组直接返回
        if (! static::accessible($array)) {
            return value($default);
        }
        //返回数组
        if (is_null($key)) {
            return $array;
        }
        //判断是否含有指定的索引
        if (static::exists($array, $key)) {
            return $array[$key];
        }
        //是否含有.符号
        if (strpos($key, '.') === false) {
            return $array[$key] ?? value($default);
        }

        foreach (explode('.', $key) as $segment) {
        //是数组且存在指定索引
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
            //否则直接返回默认值
                return value($default);
            }
        }

        return $array;
    }
```  
accessible()方法【判断是否是数组】
```php  
public static function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }
```  
exists()方法【判断数组是否存在指定的索引$key】
```php  
public static function exists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }
```  
except()方法   

```php  
 public static function except($array, $keys)
    {
        static::forget($array, $keys);

        return $array;
    }
```  
forget()方法【删除数组指定索引，支持多维数组】
```php  
public static function forget(&$array, $keys)
    {
    //数组
        $original = &$array;
//索引数组
        $keys = (array) $keys;

        if (count($keys) === 0) {
            return;
        }
        //循环key数组
        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            //数组存在指定索引则删除数据元素
            if (static::exists($array, $key)) {
                unset($array[$key]);

                continue;
            }
            //分割数组【除非这个key真的含有.符号】
            $parts = explode('.', $key);

            // clean up before each pass
            $array = &$original;
            
            while (count($parts) > 1) {
                //返回该数组的首个元素
                $part = array_shift($parts);
                //存在且是数组 
                //则获取继续
                //一般此处用于多维数组，一直获取到最后一个数组并覆盖
                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];//数据更新
                } else {
                    continue 2;
                }
            }
            //删除数据
            //此时$array可能是二级数组
            //删除最后一级数组某个指定索引 
            unset($array[array_shift($parts)]);
        }
    }
```  

first()方法  
```php  
public static function first($array, callable $callback = null, $default = null)
    {
    //回调函数是空的情况
        if (is_null($callback)) {
            if (empty($array)) {
                return value($default);
            }

            foreach ($array as $item) {
                return $item;
            }
        }

//循环数组，并且给回调函数key,value参数，同时返回$value
        foreach ($array as $key => $value) {
            if (call_user_func($callback, $value, $key)) {
                return $value;
            }
        }

        return value($default);
    }
```  
add方法  【给数组添加新的数据元素】，支持.语法多维
```php  
public static function add($array, $key, $value)
    {
    //防止复制添加
        if (is_null(static::get($array, $key))) {
            static::set($array, $key, $value);
        }

        return $array;
    }
```  

Wrap方法 【包装成数组】  
```php  
public static function wrap($value)
    {
        if (is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }
```  
shuffle混乱数组
```php  
 public static function shuffle($array, $seed = null)
    {
        if (is_null($seed)) {
            shuffle($array);
        } else {
            mt_srand($seed);
            shuffle($array);
            mt_srand();
        }

        return $array;
    }
```