#!/usr/bin/env php
<?php

list($exe, $style, $file, $output) = $argv;
define('NEW_LINE', "\n");
if (!$style){
	echo "Help: <convert-line-break> <style:crlf/cr/lf> <file:...> [output:...]".NEW_LINE;
}

$STYLES = array(
	'crlf' => "\r\n",
	'cr' => "\r",
	'lf' => "\n",
);

echo "Reading file $file...".NEW_LINE;

$lines = file($file);

echo "Start convert to $style".NEW_LINE;
if (!$STYLES[$style]){
	echo "Error: Invalid style!".NEW_LINE;
	die;
}
foreach ($lines as &$line){
	$line = trim($line, "\r\n");
}
$newContent = implode($STYLES[$style], $lines);
if ($output){
	file_put_contents($file, $newContent);
}else{
	echo $newContent;
	echo NEW_LINE;
}

