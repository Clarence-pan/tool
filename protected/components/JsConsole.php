<?php

class JsConsole {
    public static function log($msg, $_=null){
        self::$messages[] = func_get_args();
    }

    public static function output(){
        echo "<script>";
        foreach (self::$messages as $msg) {
            echo "console.log.apply(console, ", json_encode($msg), ");", PHP_EOL;
        }
        echo "</script>";
    }

    private static $messages = array();
}
