<?php
/**
 * Created by PhpStorm.
 * User: clarence
 * Date: 14-12-9
 * Time: 上午12:05
 */

namespace tool\base{
    class Dispatcher{
        public static $GET_DISPATCH_PATH_ENABLE = true;

        public function __construct(){

        }

        /**
         * 分发请求
         * @param $method  -- GET/POST...
         * @param $uri -- 通常，应该是类似module/controller/action一样的restful的请求URL
         * @param $get -- get的参数（如果uri中不能匹配，则在GET_DISPATCH_PATH_ENABLE模式下，将用get的d参数作为URI
         * @param $post -- post的参数
         * @param $cookie -- cookie的参数
         * @return mixed
         */
        public function dispatch($method, $uri, $get, $post, $cookie){
            if (self::$GET_DISPATCH_PATH_ENABLE && in_array(left_of('?', $uri), array('', '/', '/index.php'))){
                $uri = $get['d'];
            }
            preg_match('|^/?(?P<module>.*)/(?P<controller>.*)/(?P<action>.*)$|', left_of('?', $uri), $matches);
            if (!$matches){
                return false;
            }
            $module = $matches['module'];
            $controller = ucfirst($matches['controller']);
            $action = $matches['action'];
            $realController = "\\tool\\$module\\controller\\$controller".'Controller';
            $controllerInstance = new $realController();
            $params = $controllerInstance->parseParams($get, $post, $cookie);
            $controllerInstance->init();
            $realMethod = "do$method$action";
            return $controllerInstance->$realMethod($params);
        }
    }

    function left_of($needle, $haystack){
        $index = strpos($haystack, $needle);
        if ($index === false){
            return $haystack;
        } else {
            return substr($haystack, 0, $index);
        }
    }

}