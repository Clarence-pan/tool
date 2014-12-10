#! /usr/bin/env php
<?php
define('LF', chr(10)); // new line
define('CR', chr(13)); // return key
define('NEW_LINE', "\n");
function detect_dir($pwd){
//    echo 'Enter '.$pwd.NEW_LINE;
    $files = scandir($pwd);
    foreach ($files as $file) {
        $fileFullName = get_relative_path("$pwd/$file");
        if (!in_array($file, array('.', '..', '.git', '.svn'))){
            if (is_dir($fileFullName)){
                detect_dir($fileFullName);
            }else{
                $eol = detect_eol($fileFullName);
                echo  "$eol\t$fileFullName".NEW_LINE;
            }
        }
    }
//    echo "Leave $pwd".NEW_LINE;
}

function detect_eol($file){
    $eol = '??';
    $h = fopen($file, 'rb');
    while (!feof($h)){
        $c = fread($h, 1);
        if ($c == CR){
            $c = fread($h, 1);
            if ($c == LF){
                $eol = 'CRLF';
            } else {
                $eol = 'CR';
            }
            break;
        }else if ($c == LF){
            $c = fread($h, 1);
            if ($c == CR){
                $eol = 'LFCR';
            } else {
                $eol = 'LF';
            }
            break;
        }
    }
    fclose($h);
    return $eol;
}
$currentWorkingDir = getcwd();
function get_relative_path($file){
    global $currentWorkingDir;
    if (strpos($file, $currentWorkingDir) === 0){
        return substr($file, strlen($currentWorkingDir) + 1);
    }
    return $file;
}

detect_dir($currentWorkingDir);




