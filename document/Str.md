### Str类相关方法  
```php  
public static function endsWith($haystack, $needles)
    {
    //数组迭代循环
        foreach ((array) $needles as $needle) {
        //判断数组的每个数据元素是否包含【从后面开始截取】指定的字符
            if (substr($haystack, -strlen($needle)) === (string) $needle) {
                return true;
            }
        }

        return false;
    }
//判断$callback是否含有@符号，含有就拆成数组返回，否则返回默认数组
public static function parseCallback($callback, $default = null)
    {
        return static::contains($callback, '@') ? explode('@', $callback, 2) : [$callback, $default];
    }
```