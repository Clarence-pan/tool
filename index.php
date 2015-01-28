<?php
// remove the following lines when in production mode
//$_REQUEST['debug'] = 1;
defined('YII_DEBUG') or define('YII_DEBUG', !!$_REQUEST['debug']);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

require_once __DIR__.'/../framework-yii/yii.php';
Yii::createWebApplication((YII_DEBUG ? __DIR__.'/protected/config/debug.php' : __DIR__.'/protected/config/main.php'))->run();
