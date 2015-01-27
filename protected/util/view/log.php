<?PHP if(!@$_REQUEST["autoAppend"]){ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?PHP } ?>
<?php
/**
 * Created by PhpStorm.
 * User: panchangyun
 * Date: 14-9-18
 * Time: 上午10:37
 */
if (!isset($fileName)) {
    $fileName = 'd:\yii-array.log';
}
ini_set('display_errors', 'on');
error_reporting(E_ALL ^ E_STRICT );
require(__DIR__.'/log/ArrayLog.php');
require(__DIR__.'/log/CacheLog.php');
require(__DIR__.'/log/_filterLog_basic.php');
function readSqlLog($fileName='d:\yii-sql.log'){
    $content = file_get_contents($fileName);
    $code = 'return array(' . $content . ');';
    //echo $code;
    return eval_code($code);
}

function get_style_of_request($request){
    $colors = explode(' ', '#ffffff #ffddff #ddffff #ffffdd #ddddff #ddffdd #ffdddd #dddddd');
    static $requests = array();
    if (!array_key_exists($request, $requests)){
        $i = count($requests);
        $requests[$request] = $i;
    }else{
        $i = $requests[$request];
    }
    $i = $i % count($colors);
    return 'background-color: '.$colors[$i];
}

function output_logs($log, $id=100000){
    for ($logline = $log->next(); $logline; $logline = $log->next(), $id++): /* id="<?php echo $id?>" */
        if (filterLog($logline)){
            continue;
        }
        ?>
        <ul  class="log" style="<?php echo get_style_of_request($logline['request'])?>">
            <li class="line"><?php echo  $id + 1 ?>:</li>
            <?php foreach ($logline as $key => $logValue): ?>
                <li class="<?php echo  $key ?>"
                    <?php if ($key == 'msgBody'){ ?>
                        ondblclick="javascript:showInNewWindow(this);"
                        onclick="javascript:expandThisCell(this)"
                    <?php }else if ($key == 'msgHead' || $key == 'category' || $key == 'level'){ ?>
                        ondblclick="javascript:showStackTrace(this);"
                    <?php } ?>
                    ><?php echo  htmlspecialchars($logValue) ?></li>
            <?php endforeach; ?>
        </ul>
    <?PHP if ($id % 200 == 0): ?>
        <script type="application/javascript">
            scrollToBottom();
        </script>
    <?PHP endif;
    endfor;
    return $id;
}

//$log = new ArrayLog($fileName);
$log = new CacheLog();
if (@$_REQUEST['clear']){
    if (@$_REQUEST['clear'] >= $log->fileSize) {
        $log->clear();
        echo "Clear finished!";
    }else{
        echo "Already cleared! The following is new one: ";
    }
}
?>
<?PHP if(!@$_REQUEST["autoAppend"]){ ?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<script type='text/javascript' src='/static/js/jquery.js'></script>
<script type="text/javascript">
    <?php echo file_get_contents(__DIR__.'/log/log.js') ?>
</script>
<title></title>
<style type="text/css" >
    <?php echo file_get_contents(__DIR__.'/log/log.css') ?>
    li.stackTrace{
    <?PHP if (!@$_REQUEST['displayStackTrace']): ?>
        display: none ;
    <?PHP else: ?>
        display: block !important;
        position: relative !important;
        left: 11em !important;
    <?PHP endif; ?>
        white-space: pre-wrap;
    }
</style>
</head>
<body>
<?php
$links = array('interested' => 'interested');
echo file_get_contents(__DIR__.'/log/_tools.php')
?>
<?php }?>
<script type="application/javascript">
    console.log("Last modified time: <?php echo  date('Y-m-d H:i:s', $log->filemtime) ?>, file size: <?php echo  $log->fileSize ?>bytes");
</script>

<?PHP
if (@$_REQUEST['seek']){
    $log->seek(intval(@$_REQUEST['seek']));
}
$id =  @$_REQUEST["id"];
$id = $id ? $id : 0;
$id = output_logs($log, $id);
?>

<script type="application/javascript" >
    setGlobal("itemId",  <?php echo  $id ?>);
    setGlobal("fileSize", <?php echo  $log->fileSize ?>);
    scrollToBottom();
</script>
<!-- MUST RUN: // The above maybe cannot run, so eval the below:
    setGlobal("itemId",  <?php echo  $id ?>);
    setGlobal("fileSize", <?php echo  $log->fileSize ?>);
//-->