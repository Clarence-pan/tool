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
/*
 *
array (
  'time' => '2014-09-18 10:36:36',
  'level' => 'trace',
  'category' => 'system.db.CDbCommand',
  'msgHead' => 'Executing SQL',
  'msgBody' => ' INSERT INTO method_log_02	(log_pos,content,extend_id,type,msg,add_time)	VALUES(\'StatementMod::getFmisCharts::529\', "{uid:7329,token:ST-4935246-LGBrCMbt0FOtfue5T3gl-cas,nickname:\\u6f58\\u660c\\u8d5f,r:0.27386932098307,isExcel:0,agencyId:4333,agencyName:,period:,startDate:,start:0,limit:10,sortname:,sortorder:}", 0, \'2\', "获取财务报表：0", \'2014-09-18 10:36:36\');',
  'stackTrace' => ...
 * */
class ArrayLog {
    public $fileSize;
    public $filemtime;
    public $fileName;
    public function my_filesize(&$file){
        $oldPos = ftell($file);
        fseek($file, 0, SEEK_END);
        $size = ftell($file);
        fseek($file, $oldPos, SEEK_SET);
        return $size;
    }
    public function __construct($fileName){
        $this->file = fopen($fileName, "rb");
        $this->fileName = $fileName;
        $this->fileSize = $this->my_filesize($this->file);
        $this->filemtime = filemtime($fileName);
        $maxSize = 1024*1024 * 2;
        if ($this->fileSize > $maxSize && !@isset($_REQUEST['seek'])){
            @header("Location: ".$_REQUEST['HTTP_HOST'].$_REQUEST['REQUEST_URI']
                ."?seek=".($this->fileSize - $maxSize));
        }
    }
    public function seek($offset){
        fseek($this->file, $offset);
    }
    public function next(){
        $line = fgets($this->file);
        $line = 'return ' . $line . ';';
        //echo $line;
        //var_dump($line);
        $arr =  eval($line);
        foreach ($arr as &$val) {
            $val = base64_decode($val);
        }
        //var_dump($arr);
        if (feof($this->file)){
            return false;
        } else if (empty($arr)) {
            return array('--', '--', $line);
        }

        return $arr;
    }
    public function clear(){
        file_put_contents($this->fileName, "");
    }
    public function __destruct(){
        fclose($this->file);
    }
}
class CacheLog{

    public $fileSize;
    public $filemtime;
    public $fileName;

    private $cache;
    public function __construct(){
        $this->cache = new Memcache();
        $this->cache->connect('127.0.0.1', 11211);
        $fileSize = $this->cache->get('log-item-num');
        $this->fileSize = $fileSize = $fileSize ? $fileSize : 0;
        if ($fileSize > 200 && !@isset($_REQUEST['seek'])){
            $this->next = $fileSize - 200;
        }
    }
    private $next = 0;
    public function seek($pos){
        $this->next = $pos;
    }
    public function next(){
        $next = $this->next;
        $this->next = $next + 1;
        if ($this->next > $this->fileSize){
            return null;
        }
        $item = $this->cache->get("log-item-$next");
        if ($item == false){
            throw new Exception('log item missing! '.$next);
        }
//        var_dump($item);
        return $item;
    }
    public function clear(){
        $this->cache->set('log-item-num', 0);
        $this->fileSize = 0;
    }
}
function readSqlLog($fileName='d:\yii-sql.log'){
    $content = file_get_contents($fileName);
    $code = 'return array(' . $content . ');';
    //echo $code;
    return eval_code($code);
}
function filterLog($logLine){
    if (!@$_REQUEST['noFilterDbProfile']
        && $logLine['level'] == 'profile'
        && in_array($logLine['category'], array('system.db.CDbCommand.query', 'system.db.CDbCommand.execute'))
        && in_array($logLine['msgHead'], array('begin', 'end'))){
        return true;
    }
    /**
     * @var $category string
     * @var $msgHead string
     * @var $msgBody string
     * @var $request string
     */
    extract($logLine);
    if ($category == 'CWebApplication' ){
        return false;
    }
    if ($category == 'application' and strpos($msgHead, 'REST API 外部接口调用') === 0){
        return false;
    }
    if ($category == 'memcache' and strpos($msgHead, 'Memcache') === 0){
        return false;
    }
    if ($category == 'system.db.CDbCommand' and strpos($msgHead, 'Querying SQL') === 0){
        return false;
    }
    if ($category == 'system.db.CDbCommand' and strpos($msgHead, 'Executing SQL') === 0){
        return false;
    }
    return true;
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
        function showStackTrace(title){
            $(title).siblings('.stackTrace').toggle();
        }
        function getGlobal(key){
            var global = (function(){return this;})();
            if (!key){
                return global;
            }
            return global[key];
        }
        function setGlobal(key, value){
            var global = (function(){return this;})();
            global[key] = value;
        }
        function toggle_stack_trace(){
            $('.stackTrace').toggle();
        }
        function buildQuery(key, value){
            var query = getCurrentParams();
            if (typeof(key) == 'object'){
                for (var k in key){
                    query[k] = key[k];
                }
            } else if (typeof(key) == 'undefined'){
            } else {
                query[key] = value;
            }
            delete query['clear'];
            query = buildQueryString(query);
            return query;
        }
        function refresh(key, value){
            var query;
            if ('clear' == key){
                query = '?clear='+value;
            } else {
                query = buildQuery(key, value);
            }
            location.replace(query);
        }
        function getCurrentParams(){
            var params = {};
            var searches = window.location.search.substr(1).split("&")
            for (var j in searches){
                var s = searches[j];
                var i = s.indexOf('=');
                if (i>0){
                    params[s.substr(0,i)] = s.substr(i+1);
                }
            }
            return params;
        }
        function buildQueryString(params){
            var query = "?";
            for (var i in params){
                query = query + i + "=" + params[i] + "&";
            }
            return query;
        }
        function autoAppend(){
            var url = buildQuery({"seek": getGlobal('fileSize'),
                                   "autoAppend": 1,
                                   "id": getGlobal('itemId')});
            ajaxGetContent(url, true, function(content){
                var div = document.createElement('div');
                div.innerHTML = content;
                document.body.appendChild(div);
                var oldFileSize = getGlobal('fileSize');
                var trick = '<!-- MUST RUN:';
                var i = content.indexOf(trick);
                if (i > 0){
                    eval(content.substr(i + trick.length));
                }
                if (oldFileSize != getGlobal('fileSize')){
                    scrollToBottom();
                }
            });
            if (window.stopAutoAppend){
                return;
            }
            setTimeout("autoAppend()", 1000);
        }
        function ajaxGetContent(url, async, resultCallbackFunc){
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function(){
                if (xhttp.readyState == 4 && xhttp.status == 200){
                    resultCallbackFunc(xhttp.responseText);
                }
            };
            xhttp.open("GET", url, async);
            xhttp.send();
        }
        function scrollToBottom(){
//            var bottom = document.getElementById('bottom');
//            if (!bottom){
//                bottom = document.createElement("div");
//                bottom.id = 'bottom';
//            }
//            document.body.appendChild(bottom);
//            location.replace("#bottom");
            window.scrollTo(0, 9999999);
        }
        function scrollToTop(){
            window.scrollTo(0, 0);
        }
        function showInNewWindow(e){
            if (e.innerHTML.trim()[0] == '<'){
                e.innerHTML = e.innerText;
            }

            if (e.innerHTML.split("\n").length > 3){
                var x = window.open('', '_blank');
                x.document.body.innerHTML = '<pre>'+e.innerHTML+'</pre>';
            }
        }
        function expandThisCell(e){
            $(e).toggleClass('fixed-height');
        }
    </script>
<title></title>
<style type="text/css" >
    table {
        margin: 0;
        border: 0;
        padding: 0;
    }
    tr {
        margin: 0;
        padding: 0;
    }
    thead {
        margin: 0;
        border: 0;
        padding: 0;
        font-weight: bold;
    }
    tbody {
        margin: 0;
        border: 0;
        padding: 0;
    }
    thead {
        margin: 0;
        border: 0;
        padding: 0;
    }
    td {
        padding: 0;
        margin: 0;
        clear: both;
        color: rgb(51, 51, 51);
        font-size: 13px;
        line-height: 18px;
        position: relative;
        border-bottom-width: 1px;
        border-bottom-color: rgba(82, 168, 236, 0.6);
        border-bottom-style: solid;
        vertical-align: top;
    }
    body {
        background-color: rgba(152, 251, 152, 0.24);
        margin-top: 3em;
    }
    ul.log {
        margin: 0;
        padding: 0;
        border: 0;
        border-top-color: rgba(82, 168, 236, 0.6);
        border-top-width: 1px;
        border-top-style: solid;
    }
    li {
        display: inline;
        white-space: pre-wrap;
        font-size: 13px;
        color: darkblue;
    }
    li.line{
        display: inline-block;
        width: 3em;
        max-width: 3em;
    }
    li.time{
        float: right
    }
    li.level {
        display: inline-block;
        /*display: none !important;*/
        width: 6em;
        max-width: 6em;
        overflow-x: hidden;
    }
    li.category {
        display: inline-block;
        min-width: 15em;
        /*display: none !important;*/
    }
    li.msgHead {
        font-weight: bold;
        min-width: 5em;
    }
    li.msgBody {
        display: block;
        position: relative;
        left: 11em;
        white-space: pre-wrap;
        color: black;
        max-height: 8em;
        max-width: 90vw;
        overflow: hidden;
    }
    li.request{
        display: none;
    }
    li.fixed-height{
        max-height: initial !important;
    }
    li.line {

    }
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
    .tools{
        position: fixed;
        left: 20vw;
        top: 1vh;
        z-index: 10;
    }
</style>
</head>
<body>
<div class="tools">
    <form method="GET" action="<?php echo  $_SERVER['REQUEST_URI'] ?>" >
        <input type="button" onclick="refresh()" value="Refresh">
        <input type="button" onclick="refresh('clear', getGlobal('fileSize') || 9999999)" Value="Clear" />
        <input type="button" onclick="refresh('displayStackTrace',1)" Value="Display Stack Trace" />
        <input type="button" onclick="refresh('seek', getGlobal('fileSize'))" value="See new"/>
        <input type="button" onclick="window.stopAutoAppend = false; autoAppend();" value="Auto append" />
        <input type="button" onclick="window.stopAutoAppend = true" value="Stop auto append" />
        <input type="button" onclick="scrollToTop()" value="Top" />
        <input type="button" onclick="scrollToBottom()" value="Bottom" />
        <a href="view">View All</a>

    </form>
</div>
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