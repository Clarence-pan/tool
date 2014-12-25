#!/usr/bin/env php
<?php


$input = fgets(STDIN);
while (!feof(STDIN)){
    $input .= fgets(STDIN);
}
$afterBase64Decode = base64_decode($input);
if ($afterBase64Decode === false){
    fputs(STDERR, 'Error: Base64 decode failed! Input is: '.$input);
    return 1;
}
$afterJsonDecode = json_decode($afterBase64Decode, true);
if ($afterJsonDecode === false){
    fputs(STDERR, 'Error: Json decode failed! Input is: '.$input.PHP_EOL.'After base64 decode: '.$afterBase64Decode);
    return 2;
}
fputs(STDOUT, var_export($afterJsonDecode, true));
fputs(STDOUT, PHP_EOL);
return 0;



