### Symfony\Polyfill\Php72类  

```php  
public static function sapi_windows_vt100_support($stream, $enable = null)
    {
    //不是流直接触发错误
        if (!\is_resource($stream)) {
            trigger_error('sapi_windows_vt100_support() expects parameter 1 to be resource, '.\gettype($stream).' given', E_USER_WARNING);

            return false;
        }
        //https://www.php.net/manual/zh/function.stream-get-meta-data.php 
        //得到流的相关信息  
        /**
        
        Array
        (
            [timed_out] =>
            [blocked] => 1
            [eof] =>
            [wrapper_type] => PHP
            [stream_type] => STDIO
            [mode] => w
            [unread_bytes] => 0
            [seekable] => 1
            [uri] => php://output//标准输出流
        **/
        $meta = stream_get_meta_data($stream);
        //判断流类型  看你fopen("php://output")落实是php://stdio
        if ('STDIO' !== $meta['stream_type']) {
            trigger_error('sapi_windows_vt100_support() was not able to analyze the specified stream', E_USER_WARNING);

            return false;
        }

        // We cannot actually disable vt100 support if it is set
        if (false === $enable || !self::stream_isatty($stream)) {
            return false;
        }

        // The native function does not apply to stdin
        //将数组数据转换为小写
        $meta = array_map('strtolower', $meta);
        //$stdin结果为false
        $stdin = 'php://stdin' === $meta['uri'] || 'php://fd/0' === $meta['uri'];
        //win7测试为假  
        //不同的系统可能结果不一样【请自测试】
        return !$stdin
            && (false !== getenv('ANSICON')//
            || 'ON' === getenv('ConEmuANSI')
            || 'xterm' === getenv('TERM')
            || 'Hyper' === getenv('TERM_PROGRAM'));
    }
```