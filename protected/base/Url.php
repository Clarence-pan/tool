<?php
namespace tool\base;

class Url extends Object
{
    private $path = null;

    /**
     * @param $path string - /path/to/something
     *               array - [ '/path/to/something' => [ 'param1' => 'value1', ...] ]
     */
    public function __construct($path){
        $this->path = $path;
    }

    public function __toString($full=false){
        return $this->generateUrl($full);
    }

    public function generateUrl($full=false){
        if (is_array($this->path)){
            foreach ($this->path as $_url => $params) {
                $url = $_url;
                if (strpos(self::getAppBaseUrl(), '?') !== false){
                    $connector = '&';
                }else{
                    $connector = '?';
                }
                $url .= $connector . http_build_query($params);
                break;
            }
        }else{
            $url = strval($this->path);
        }
        return self::getAppBaseUrl().$url;
    }

    public static function getAppBaseUrl(){
        return self::$_appBaseUrl;
    }

    public static function setAppBaseUrl($baseUrl){
        self::$_appBaseUrl = $baseUrl;
    }

    private static $_appBaseUrl = 'index.php?d=';
}