<?php

$str = "hello, hell world!";
$from = array('hello', 'hell', 'world');
$to = array('hi', 'heaven', 'man');
$map = array_combine($from, $to);
$format = '%s, %s %s!';
define('COUNT', 100000);

$startTime = microtime(true);
for ($i = 0; $i < COUNT; $i++){
    $result = str_replace($from, $to, $str);
}
$endTime = microtime(true);
printf("Total: %.4f(s). Once: %.8f(s). -- str_replace".PHP_EOL, $endTime - $startTime, ($endTime - $startTime)/COUNT);

$startTime = microtime(true);
for ($i = 0; $i < COUNT; $i++){
    $result = strtr($str, $map);
}
$endTime = microtime(true);
printf("Total: %.4f(s). Once: %.8f(s). -- strtr".PHP_EOL, $endTime - $startTime, ($endTime - $startTime)/COUNT);

$startTime = microtime(true);
for ($i = 0; $i < COUNT; $i++){
    $result = sprintf($format, 'hi', 'heaven', 'man');
}
$endTime = microtime(true);
printf("Total: %.4f(s). Once: %.8f(s). -- sprintf".PHP_EOL, $endTime - $startTime, ($endTime - $startTime)/COUNT);

// Windows 7, PHP 5.3.4
//Total: 0.2780(s). Once: 0.00000278(s). -- str_replace
//Total: 0.1823(s). Once: 0.00000182(s). -- strtr
//Total: 0.1830(s). Once: 0.00000183(s). -- sprintf

// Ubuntu linux 14.04, PHP 5.3.4
//Total: 0.1441(s). Once: 0.00000144(s). -- str_replace
//Total: 0.0705(s). Once: 0.00000071(s). -- strtr
//Total: 0.0534(s). Once: 0.00000053(s). -- sprintf