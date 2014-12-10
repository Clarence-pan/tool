#!/usr/bin/env php
<?php

define('NEW_LINE', "\n");


function scan_dir($pwd, $action){
//    echo 'Enter '.$pwd.NEW_LINE;
    $files = scandir($pwd);
    foreach ($files as $file) {
        $fileFullName = get_relative_path("$pwd/$file");
        if (!in_array($file, array('.', '..', '.git', '.svn'))){
            call_user_func($action, $fileFullName, $pwd, $file);
            if (is_dir($fileFullName)){
                scan_dir($fileFullName, $action);
            }
        }
    }
//    echo "Leave $pwd".NEW_LINE;
}

function get_relative_path($file){
    global $currentWorkingDir;
    if (strpos($file, $currentWorkingDir) === 0){
        return substr($file, strlen($currentWorkingDir) + 1);
    }
    return $file;
}
function get_file_ext($file){
    $parts = explode('.', $file);
    return end($parts);
}
function extend_php_short_tag($inFile, $outFile){
    if (strtolower(get_file_ext($inFile)) != 'php'){
        return;
    }
    $fileContent = file_get_contents($inFile);
    if ($fileContent){
        echo 'process '.$inFile.NEW_LINE;
        $fileContent = str_replace('<?php', '<?php', $fileContent);
        $fileContent = str_replace('<?php=', '<?php echo', $fileContent);
        $fileContent = str_replace('<?phpphp', '<?php', $fileContent);
        file_put_contents($outFile, $fileContent);
    }
}

$pwd = getcwd();

scan_dir($pwd, function($file){
    extend_php_short_tag($file, $file);
});
