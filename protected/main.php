<?php
/**
 * Created by PhpStorm.
 * User: clarence
 * Date: 14-12-9
 * Time: 上午12:17
 */
namespace{
    ini_set('display_errors', 'on');
    error_reporting(E_ALL ^ E_STRICT ^ E_NOTICE);

    function is_debug_enable(){
        return true;
    }

    function debug($msg){
        if (!is_debug_enable()){
            return ;
        }
        $args = func_get_args();
        echo "\n<!--";
        foreach ($args as $arg){
            if (is_string($arg)){
                echo $arg;
            }else{
                var_dump($arg);
            }
        }
        echo "\n-->";
    }
}
namespace tool{

    /**
     * 自动加载类
     * @param $class
     */
    function auto_load($class)
    {
        // 命名空间转化为目录分隔符
        $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        // 去掉当前的命名空间
        $class = preg_replace('|^'.DIRECTORY_SEPARATOR.'?tool'.DIRECTORY_SEPARATOR.'|i', '', $class);
        $fileMaps = array(
            function ($class){
                return $class.'.php';
            },
            function ($class){
                return $class.'.inc.php';
            }
        );
        foreach ($fileMaps as $map) {
            $file = dirname(__FILE__).DIRECTORY_SEPARATOR.$map($class);
            debug('load file: '.$file.' '.is_file($file));
            if (is_file($file)){
                require_once($file);
            }
        }
    }

    spl_autoload_register('\tool\auto_load');


    class Main{

        public static function run($config){
            $instance = new self($config);
            return $instance->process();
        }

        public function __construct($config){
            $this->config = $config;
            $this->dispatcher = new base\Dispatcher($config['route']);
        }

        public function process(){
            return $this->dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_GET, $_POST, $_COOKIE);
        }

        private $config;
        private $dispatcher;
    }

}
//
//
//<VirtualHost *:80>
//ServerName tool.local.me
//	DocumentRoot /home/clarence/jd/tool
//
//	# Available loglevels: trace8, ..., trace1, debug, info, notice, warn,
//	# error, crit, alert, emerg.
//	# It is also possible to configure the loglevel for particular
//	# modules, e.g.
//	#LogLevel info ssl:warn
//
//	ErrorLog ${APACHE_LOG_DIR}/tool.local.me-error.log
//	CustomLog ${APACHE_LOG_DIR}/tool.local.me-access.log combined
//#        LogLevel trace7 rewrite:trace8
//	LogLevel alert
//# For most configuration files from conf-available/, which are
//# enabled or disabled at a global level, it is possible to
//# include a line for only one particular virtual host. For example the
//# following line enables the CGI configuration for this host only
//# after it has been globally disabled with "a2disconf".
//#Include conf-available/serve-cgi-bin.conf
//<Directory "/home/clarence/jd/tool">
//AllowOverride None
//            Options Includes FollowSymLinks Indexes
//            Require all granted
//            RewriteEngine On
//#            RewriteCond %{REQUEST_FILENAME} !-f
//            RewriteRule .* index.php   [L,QSA]
//</Directory>
//</VirtualHost>
//



