#!/usr/bin/env php
<?php


$input = fgets(STDIN);
while (!feof(STDIN)){
    $input .= fgets(STDIN);
}
$afterJsonDecode = json_decode($input, true);
if ($afterJsonDecode === false){
    fputs(STDERR, 'Error: Json decode failed! Input is: '.$input.PHP_EOL);
    return 2;
}
fputs(STDOUT, var_export($afterJsonDecode, true));
fputs(STDOUT, PHP_EOL);
return 0;



