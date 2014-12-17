<?php
################################################################################################################################################
$start = microtime(true);
//xhprof_enable(XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY );
################################################################################################################################################

// PC: 2.6s, UB: 2.3s
//$t = 0;
//for($i = 0; $i < 10000000; $i++){
//    $t += $i;
//}

// PC: 5.2s, UB: 2.4s
//$t = 0;
//for ($i = 0; $i < 100000; $i++){
//    $t += is_file('/d');
//}

// PC: 5.2s, UB: 2.2s
//$t = 0;
//for ($i = 0; $i < 100000; $i++){
//    $t += is_dir('/d');
//}

// PC: 0.25, UB: 2.46   *************
//$t = 0;
//for ($i = 0; $i < 100000; $i++){
//    $t .= json_encode(array('count' => 1, 'rows' => array('test'=>'Hello world!')));
//}


// PC: 0.08s, UB: 1.44   *************
//class Yii{
//    static $app;
//    public static function  app(){
//        if (!self::$app){
//            self::$app = (object)array(
//                'charset' => 'UTF-8'
//            );
//        }
//        return self::$app;
//    }
//}
//require(dirname(__FILE__).'/../framework/web/helpers/CJSON.php');
//$t = 0;
//for ($i = 0; $i < 1000; $i++){
//    $t .= CJSON::encode(array('count' => 1, 'rows' => array('test'=>'Hello world!')));
//}

// PC: 0.084s UB: 1.40s
//class Yii{
//    static $app;
//    public static function  app(){
//        if (!self::$app){
//            self::$app = (object)array(
//                'charset' => 'UTF-8'
//            );
//        }
//        return self::$app;
//    }
//}
//require(dirname(__FILE__).'/../framework/web/helpers/CJSON.php');
//$t = 0;
//for ($i = 0; $i < 1000; $i++){
//    $t = CJSON::encode(array('count' => 1, 'rows' => array('test'=>'Hello world!')));
//}


// simple string concat:
// PC: 13s >> UB: 2.4s
//$t = '';
//for ($i = 0; $i < 10000000; $i++){
//    $t .= "Hello world!";
//}






// gettype
// PC: 0.1 UB: 2.1      *************
//$t = '';
//$a = array();
//for ($i = 0; $i < 100000; $i++){
//    $t = gettype($a);
//}

// PC: 0.19s UB: 2.9s  *************
//class Yii{
//    static $app;
//    public static function  app(){
//        if (!self::$app){
//            self::$app = (object)array(
//                'charset' => 'UTF-8'
//            );
//        }
//        return self::$app;
//    }
//}
//$t = '';
//$a = array();
//for ($i = 0; $i < 100000; $i++){
//    $t = Yii::app();
//}

//function_exists
//0.16 2.2
//$t = '';
//$a = array();
//for ($i = 0; $i < 100000; $i++){
//    $t = function_exists('json_encode');
//}


// strtoupper
// 0.16 2.2
//$t = '';
//$a = array();
//for ($i = 0; $i < 100000; $i++){
//    $t = strtoupper('Hello world!');
//}

// array_map
// 0.05 1.08
//$t = '';
//$a = array();
//for ($i = 0; $i < 10000; $i++){
//    $t = array_map(function($x){ return $x;}, array(1,2,3));
//}


// branch
// 3.7 2.8
//$t = '';
//$a = array();
//for ($i = 0; $i < 10000000; $i++){
//    if ($i % 2 == 0){
//        $t = $i;
//    }
//}

// assign object
// 2.8 2.0
//$t = '';
//$a = (object)array('a' => 1);
//for ($i = 0; $i < 10000000; $i++){
//    $t = $a;
//}

// assign object and get field
// 3.4 2.7
//$t = '';
//$a = (object)array('a' => 1);
//for ($i = 0; $i < 10000000; $i++){
//    $t = $a->a;
//}






################################################################################################################################################
$end = microtime(true);
$delta = $end - $start;
echo "start: $start end: $end ==> usage: $delta\n";


//$data = xhprof_disable();   //返回运行数据
//
//// xhprof_lib在下载的包里存在这个目录,记得将目录包含到运行的php代码中
//include_once "xhprof_lib/utils/xhprof_lib.php";
//include_once "xhprof_lib/utils/xhprof_runs.php";
//$objXhprofRun = new XHProfRuns_Default();
//// 第一个参数j是xhprof_disable()函数返回的运行信息
//// 第二个参数是自定义的命名空间字符串(任意字符串),
//// 返回运行ID,用这个ID查看相关的运行结果
//$run_id = $objXhprofRun->save_run($data, "test");
//echo "xhprof-report(BUCKBEEK):  http://xhprof.pcy.tuniu.org/index.php?run=$run_id&source=test  \n";
################################################################################################################################################
