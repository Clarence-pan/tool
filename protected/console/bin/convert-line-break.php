#!/usr/bin/env php
<?php

/**
 * @param $file
 * @param $style - line break style
 * @return string
 */
function convertLineBreak($file, $style) {
    $STYLES = array(
        'crlf' => "\r\n",
        'cr' => "\r",
        'lf' => "\n",
    );

//    echo "Reading file $file..." . NEW_LINE;

    $lines = file($file);

//    echo "Start convert to $style" . NEW_LINE;
    if (!$STYLES[$style]) {
        echo "Error: Invalid style!" . NEW_LINE;
        die;
    }
    foreach ($lines as &$line) {
        $line = trim($line, "\r\n");
    }
    $newContent = implode($STYLES[$style], $lines);
    file_put_contents($file, $newContent);
}

require(dirname(__FILE__) . '/../lib/File.php');
use tool\console\lib\File as File;

if ($argv[1] == '-r') {
    list($exe, $opt, $style, $dir) = $argv;
    if (!$dir) {
        $dir = '.';
    }


    File::scanDir($dir, function ($file) use($style) {
        if (!is_dir($file)){
            echo 'process '.$file."\n";
            convertLineBreak($file, $style);
        }
    });
} else {

    list($exe, $style, $file) = $argv;
    define('NEW_LINE', "\n");
    if (!$style) {
        echo "Help: <convert-line-break> <style:crlf/cr/lf> <file:...>" . NEW_LINE;
        echo "      <convert-line-break> -r <style:crlf/cr/lf> [dir:...(default:.)]" . NEW_LINE;
    } else {

        convertLineBreak($file, $style);
    }
}