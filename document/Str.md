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
```