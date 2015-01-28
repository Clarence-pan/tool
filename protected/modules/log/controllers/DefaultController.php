<?php

class DefaultController extends Controller
{
    public $layout = 'main';

    public function init()
    {
    }
	public function actionIndex()
	{
		$this->render('index');
	}

    public function actionInterested(){
        $this->render('index', array('filter' => 'interested'));
    }
    public function actionCache($key=null){
        $cacheLog = new \log\models\CacheLog();
        $displayKey = function($key) use ($cacheLog){
            $value = $cacheLog->getCache($key);
            echo '<pre>'.PHP_EOL;
            var_dump($value);
            echo '</pre>'.PHP_EOL;
        };
        if ($key !== null){
            $displayKey($key);
        } else {
            $num = $cacheLog->count();
            echo "<h2>Total log item number: $num</h2>".PHP_EOL;
            for ($i = 0; $i < $num; $i++){
                echo "<h3>LOG#$i</h3>".PHP_EOL;
                $displayKey(\log\models\CacheLog::LOG_ITEM_PREFIX.$i);
            }
        }
    }

}