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
fputs(STDOUT, $afterBase64Decode);
return 0;



