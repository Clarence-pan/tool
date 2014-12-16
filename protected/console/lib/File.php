<?php


namespace tool\console\lib;
error_reporting(E_ALL);
define('NEW_LINE', "\n");
class File {

    /**
     * scan dir and do an action
     * @param $pwd
     * @param $action
     * @param $rec  -- scan the whole sub-tree recursively
     */
    public static function scanDir($pwd, $action, $rec=true){
        $files = scandir($pwd);
        foreach ($files as $file) {
            $fileFullName = self::getRelativePath("$pwd/$file");
            if (!in_array($file, array('.', '..', '.git', '.svn'))){
                call_user_func($action, $fileFullName, $pwd, $file);
                if ($rec && is_dir($fileFullName)){
                    self::scanDir($fileFullName, $action);
                }
            }
        }
    }

    /**
     * get relative path of file/dir
     * @param      $file
     * @param null $cwd
     * @return string
     */
    public static function getRelativePath($file, $cwd=null){
        if (!$cwd){
            $cwd = getcwd();
        }
        if (strpos($file, $cwd) === 0){
            $result = substr($file, strlen($cwd) + 1);
            if (strlen($result) == 0){
                return '.';
            }else{
                return $result;
            }
        }
        return $file;
    }

    /**
     * get the extension name of the file
     * @param $file
     * @return string like 'php' for *.php
     */
    public static function getFileExt($file){
        $parts = explode('.', $file);
        return end($parts);
    }
} 