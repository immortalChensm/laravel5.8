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
forget()方法   
```php  
public static function forget(&$array, $keys)
    {
        $original = &$array;

        $keys = (array) $keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            if (static::exists($array, $key)) {
                unset($array[$key]);

                continue;
            }

            $parts = explode('.', $key);

            // clean up before each pass
            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

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