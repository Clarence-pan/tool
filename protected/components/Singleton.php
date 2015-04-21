<?php


class Singleton {
    public static function instance($param=null){
        if (!self::$_instance){
            self::$_instance = new static($param);
        }
        return self::$_instance;
    }

    private static $_instance;
} 