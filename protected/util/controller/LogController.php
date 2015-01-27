<?php
namespace tool\util\controller;

ini_set('display_errors', 'on');
error_reporting(E_ALL ^ E_STRICT );
use tool\base\Controller as Controller;

class LogController extends Controller {

    public function __construct(){
        $this->setTitle('View LOG');
        $this->setLayout('log/main');
    }

    public function doGetView(){
        $this->render('log');
    }

    public function doPostView(){
        $this->render('log');
    }

    public function doGetInterested(){
        $this->render('log', array('filter' => 'interested'));
    }
    public function doPostInterested(){
        $this->render('log', array('filter' => 'interested'));
    }

    public function doGetCache($param){
        $key = $param['key'];
        $cacheLog = new \tool\util\model\log\CacheLog();
        $displayKey = function($key) use ($cacheLog){
            $value = $cacheLog->getCache($key);
            echo '<pre>'.PHP_EOL;
            var_dump($value);
            echo '</pre>'.PHP_EOL;
        };
        if ($key !== null){
            $displayKey($key);
        } else {
            $num = $cacheLog->getCache('log-item-num');
            echo "<h2>Total log item number: $num</h2>".PHP_EOL;
            for ($i = 0; $i < $num; $i++){
                echo "<h3>LOG#$i</h3>".PHP_EOL;
                $displayKey('log-item-'.$i);
            }
        }
    }
} 