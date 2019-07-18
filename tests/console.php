<?php
/**
 * Created by PhpStorm.
 * User: 1655664358@qq.com
 * Date: 2019/7/18
 * Time: 15:32
 */
//$argv = $_SERVER['argv'];
//
//
//array_shift($argv);
//print_r($argv);
//echo \function_exists('php_uname') ? php_uname('s') : ''.PHP_EOL;
//echo getenv('OSTYPE').PHP_EOL;
//echo PHP_OS.PHP_EOL;
//
//$checks = [
//    //php_uname('s')返回系统名称如Windows NT Linux
//    //getenv('OSTYPE') win返回WINNT linux下没有
//    \function_exists('php_uname') ? php_uname('s') : '',
//    getenv('OSTYPE'),
//    PHP_OS,//linux 返回Linux
//];
//print_r($checks);
//echo implode(';', $checks);
////win下就是Windows NT;WINNT
//echo false !== stripos(implode(';', $checks), 'OS400');

$outstream = fopen("php://stdout","w");
fwrite($outstream,"hi");
echo getenv('TERM_PROGRAM');
//echo sapi_windows_vt100_support($outstream);
$meta = stream_get_meta_data($outstream);
$meta = array_map('strtolower', $meta);
print_r($meta);
$stdin = 'php://stdin' === $meta['uri'] || 'php://fd/0' === $meta['uri'];
//echo $stdin;
//echo getenv('ANSICON');
//echo  !$stdin
//    && (false !== getenv('ANSICON')
//        || 'ON' === getenv('ConEmuANSI')
//        || 'xterm' === getenv('TERM')
//        || 'Hyper' === getenv('TERM_PROGRAM'));

$stat = @fstat($outstream);

echo  $stat ? 0020000 === ($stat['mode'] & 0170000) : false;

print_r(parse_url("http://localhost"));