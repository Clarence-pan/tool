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

        public function __construct($route){
            $this->route = $route;

            // 允许GET,POST这样的缩写method，但为了处理方便，予以展开
            foreach ($this->route as $path => &$methods) {
                foreach ($methods as $method => $normalPath) {
                    if (strpos($method, ',') !== false){
                        foreach (explode(',', $method) as $m) {
                            $methods[$m] = $normalPath;
                        }
                    }
                }
            }

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
            $url = left_of('?', $uri);
            foreach ($this->route as $path => $methods) {
                $realPathPattern = $methods[$method];
                if (!$realPathPattern){
                    continue;
                }

                $matches = $this->matchPath($path, $url);
                if ($matches === false){
                    continue;
                }
                debug('matches path: '.$path, $matches);

                $matches['method'] = $method;

                $realPath = $this->fillPattern($realPathPattern, $matches);
                if (!$realPath){
                    continue;
                }

                $run = $this->findTheRightPathAndRunIt($realPath, $get, $post, $cookie);
                if ($run){
                    return $run;
                }
            }
            throw new \Exception('No route found! Route: '.var_export($this->route, true));
        }

        /**
         * @param $path string like '<module>/<controler>/<action>'
         * @param $url string like ajax/list/all
         * @return array('key' => 'value') when success; otherwise false;
         */
        public function matchPath($path, $url){
            $preg = $this->convertReToPhpPreg($path);
            if (preg_match($preg, $url, $matches)){
                $result = array();
                // remove integer keys
                foreach ($matches as $key => $value) {
                    if (!is_int($key)){
                        $result[$key] = $value;
                    }
                }
                return $result;
            }
            return false;
        }

        /**
         * 填充模式
         * @param $pattern string 模式，如 <module>/<controller>
         * @param $params array 参数，如 [ 'module'=> 'test', 'controller' => 'list']
         * @return bool|mixed 如果成功返回填充后的字符串，否则返回false
         */
        public function fillPattern($pattern, array $params){
            $result = $pattern;
            foreach ($params as $name => $value) {
                $result = str_replace("<$name>", $value, $result);
            }
            if (strpos('<', $result) === false && strpos('>', $result) === false){
                return $result;
            }
            return false;
        }


        /**
         * 执行
         * @param $path
         * @param $get
         * @param $post
         * @param $cookie
         * @return array|bool
         */
        public function findTheRightPathAndRunIt($path, $get, $post, $cookie){
            preg_match('|^/?(?P<module>.*)/(?P<controller>.*)/(?P<method>.*)/(?P<action>.*)$|', $path, $matches);
            if (!$matches){
                return false;
            }
            $module = $matches['module'];
            $controller = ucfirst($matches['controller']);
            $method = $matches['method'];
            $action = $matches['action'];
            $realController = "\\tool\\$module\\controller\\$controller".'Controller';
            $controllerInstance = new $realController();
            $params = $controllerInstance->parseParams($get, $post, $cookie);
            $controllerInstance->init();
            $realMethod = "do$method$action";
            return array(true, $controllerInstance->$realMethod($params));
        }

        /**
         * convert /<a:.*>/<b> to |^/(?P<a>.*)/(?P<b>.*)
         * @param $re string
         * @return string
         */
        public function convertReToPhpPreg($re){
            $s = preg_replace_callback('|<(\w+)>|', function($m){
                return "(?P<".$m[1].'>.*)';
            }, $re);
            $s = preg_replace_callback('|<(\w+):([^<>]*)>|', function($m){
                return "(?P<".$m[1].'>'.$m[2].')';
            }, $s);
            return "|^$s$|";
        }


        private $route = array();
    }

    /**
     * 左侧的xx
     * @param $needle
     * @param $haystack
     * @return string
     */
    function left_of($needle, $haystack){
        $index = strpos($haystack, $needle);
        if ($index === false){
            return $haystack;
        } else {
            return substr($haystack, 0, $index);
        }
    }


}