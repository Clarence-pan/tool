<?php

class DefaultController extends Controller
{
    public $layout = 'main';

    public function init()
    {
    }

	public function actionIndex($start=0, $limit=500, $filter='basic', $data=array())
	{
		$this->render('index', array_merge(compact('start', 'limit', 'filter'), $data));
	}

    public function actionInterested($start=0, $limit=500){
        $this->actionIndex($start, $limit, 'interested');
    }

    public function actionSummary($start=0, $limit=50000){
        $_GET['sum'] = true;
        $this->actionIndex($start, $limit, 'interested', array('summaryOnly' => true));
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

    public function actionHelp(){
        $this->render('help');
    }

    public function actionDisableStackTrace(){
        $this->actionEnableStackTrace(true);
    }
    public function actionEnableStackTrace($enable=true){
        $file = 'D:\\workspaces\\framework\\my_yii_log.php';
        $lines = file($file);
        foreach ($lines as &$line) {
            if (strpos($line, "define('YII_DEBUG_TRACE_STACK',") === 0){
                $line = "define('YII_DEBUG_TRACE_STACK', $enable);".PHP_EOL;
                break;
            }
        }
        $result = implode(PHP_EOL, $lines);
        file_put_contents($file, $result);
        echo "<pre>";
        echo $result;
        echo "</pre>";
    }
}