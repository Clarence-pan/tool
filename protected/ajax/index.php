<?php
class AjaxRequest extends ModelBase{
    private $url;
    private $params;
    private $method;
    private $id;
    public function __construct($params){
        parent::__construct();
        $this->id = $params['id'];
        if (isset($params['url'])){
            $this->url = $params['url'];
            $this->params = $params['params'];
        } else {
            $this->url = tools::leftOf("?", $params['uri'], 'FAIL_RETURN_ALL');
            $this->params = tools::rightOf("?", $params['uri'], 'FAIL_RETURN_NULL');
        }
        $this->method = $params['method'];
        $this->method = $this->method ? strtoupper($this->method) : 'GET';
    }
    public function load(){
        $sql = "SELECT url, params, method FROM request WHERE id=:id";
        $sql = $this->fillSqlArgs($sql);
        $result = $this->queryRow($sql);
        $this->url = $result['url'];
        $this->params = $result['params'];
        $this->method = $result['method'];
    }
    public function save(){
        $id = $this->id;
        if ($id === null){
            $sql = "INSERT INTO request (url, params, method) VALUES (:url, :params, :method)";
        } else {
            $sql = "UPDATE request SET url=:url, params=:params, method=:method WHERE id=:id";
        }
        $sql = $this->fillSqlArgs($sql);
        $this->execSql($sql);
        if ($id === null){
            $this->id = $id = $this->getLastInsertId();
        }
    }

    /**
     * @param mixed $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string $method
     */
    public function setMethod($method) {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * @param null|string $params
     */
    public function setParams($params) {
        $this->params = $params;
    }

    /**
     * @return null|string
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * @param null|string $url
     */
    public function setUrl($url) {
        $this->url = $url;
    }

    /**
     * @return null|string
     */
    public function getUrl() {
        return $this->url;
    }
}
class AjaxResponse extends ModelBase{
    private $id;
    private $body;
    private $format;
    private $cache;
    public function __construct($params){
        parent::__construct();
        $this->id = $params['id'];
        $this->body = $params['body'];
        $this->format = $params['format'];
        $this->cache = $params['cache'];
    }
    public function load(){
        $sql = "SELECT body, format, cache FROM response WHERE id=:id";
        $sql = $this->fillSqlArgs($sql);
        $result = $this->queryRow($sql);
        $this->body = $result['body'];
        $this->format = $result['format'];
        $this->cache = $result['cache'];
    }
    public function save(){
        $id = $this->id;
        if ($id === null){
            $sql = "INSERT INTO response (body, format, cache) VALUES (:body, :format, :cache)";
        } else {
            $sql = "UPDATE response SET body=:body, format=:format, cache=:cache WHERE id=:id";
        }
        $sql = $this->fillSqlArgs($sql);
        $this->execSql($sql);
        if ($id === null){
            $this->id = $id = $this->getLastInsertId();
        }
    }

    /**
     * @param mixed $body
     */
    public function setBody($body) {
        $this->body = $body;
    }

    /**
     * @return mixed
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * @param mixed $cache
     */
    public function setCache($cache) {
        $this->cache = $cache;
    }

    /**
     * @return mixed
     */
    public function getCache() {
        return $this->cache;
    }

    /**
     * @param mixed $format
     */
    public function setFormat($format) {
        $this->format = $format;
    }

    /**
     * @return mixed
     */
    public function getFormat() {
        return $this->format;
    }

    /**
     * @param mixed $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }
}

class AjaxHistory extends ModelBase{
    private $id;
    private $request_id;
    private $_request;
    private $response_id;
    private $_response;
    private $add_time;
    private $is_deleted;
    public function __construct($params){
        parent::__construct();
        $this->id = $params['id'];
        $this->request_id = $params['request_id'];
        $this->response_id = $params['response_id'];
        $this->add_time = $params['add_time'];
        $this->is_deleted = $params['is_deleted'];
        $this->_request = new AjaxRequest(array('id' => $this->request_id));
        $this->_response = new AjaxResponse(array('id' => $this->response_id));
    }
    public function load(){
        $sql = "SELECT request_id, response_id, add_time, is_deleted FROM history WHERE id=:id";
        $sql = $this->fillSqlArgs($sql);
        $result = $this->queryRow($sql);
        foreach ($result as $name => $value) {
            $this->$name = $value;
        }
        $this->_request->load();
        $this->_response->load();
    }
    public function save(){
        $id = $this->id;
        if ($id === null){
            $sql = "INSERT INTO history (request_id, response_id, add_time, is_deleted) VALUES (:request_id, :response_id, :add_time, :is_deleted)";
        } else {
            $sql = "UPDATE history SET request_id=:request_id, response_id=:response_id, add_time=:add_time, is_deleted=:is_deleted WHERE id=:id";
        }
        $sql = $this->fillSqlArgs($sql);
        $this->execSql($sql);
        if ($id === null){
            $this->id = $id = $this->getLastInsertId();
        }
    }
    public function delete(){
        $this->is_deleted = true;
    }

    /**
     * @param mixed $add_time
     */
    public function setAddTime($add_time) {
        $this->add_time = $add_time;
    }

    /**
     * @return mixed
     */
    public function getAddTime() {
        return $this->add_time;
    }

    /**
     * @param mixed $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed $is_deleted
     */
    public function setIsDeleted($is_deleted) {
        $this->is_deleted = $is_deleted;
    }

    /**
     * @return mixed
     */
    public function getIsDeleted() {
        return $this->is_deleted;
    }

    /**
     * @param mixed $request_id
     */
    public function setRequestId($request_id) {
        $this->request_id = $request_id;
    }

    /**
     * @return mixed
     */
    public function getRequestId() {
        return $this->request_id;
    }

    /**
     * @param mixed $response_id
     */
    public function setResponseId($response_id) {
        $this->response_id = $response_id;
    }

    /**
     * @return mixed
     */
    public function getResponseId() {
        return $this->response_id;
    }

}
class AjaxHistoryManager{
    public function __construct(){

    }
}
class AjaxGroup extends ModelBase{
    private $id;
    private $name;
    private $_urls;
    private $_urlExamples;
    public function __construct($params){
        parent::__construct();
        foreach ($params as $key => $value) {
            $this->$key = $value;
        }
    }
    public function load(){
        $sql = "SELECT name FROM group WHERE id=:id";
        $sql = $this->fillSqlArgs($sql);
        $result = $this->queryRow($sql);
        foreach ($result as $key => $value) {
            $this->$key = $value;
        }
    }
    public function save(){
        $id = $this->id;
        if ($id === null){
            $sql = "INSERT INTO group (name) VALUES (:name)";
        } else {
            $sql = "UPDATE group SET name=:name WHERE id=:id";
        }
        $sql = $this->fillSqlArgs($sql);
        $this->execSql($sql);
        if ($id === null){
            $this->id = $id = $this->getLastInsertId();
        }
    }

    public function getUrls(){
        if ($this->_urls === null){
            $this->_urls = $this->fetchUrls();
        }
        return $this->_urls;
    }

    public function fetchUrls(){
        $sql = 'SELECT DISTINCT(url) FROM request LEFT JOIN `example` ON request.`id` = `example`.`request_id` WHERE `example`.`group_id` = :id';
        $sql = $this->fillSqlArgs($sql);
        $result = $this->queryAll($sql);
        return array_values($result);
    }

    public function getUrlExamples($url){
        if (!$this->_urlExamples[$url]){
            $this->_urlExamples[$url] = $this->fetchUrlExamples($url);
        }
        return $this->_urlExamples[$url];
    }

    public function fetchUrlExamples($url){
        $sql = 'SELECT `example`.id AS id, group_id, `example`.name AS `name`, request_id, response_id ' .
            'FROM `example` LEFT JOIN request ON request.`id` = `example`.`request_id` ' .
            'WHERE `example`.`group_id` = :group_id AND request.url = :url';
        $sql = $this->_db->fillSqlArgs($sql, array('group_id' => $this->id, 'url' => $url));
        $rows = $this->queryAll($sql);
        $examples = array();
        foreach ($rows as $row) {
            $examples[$url] = new AjaxExample($row);
        }

        return $examples;
    }

    public function removeUrl($url){
        // todo...removeUrl
    }

    /**
     * @param mixed $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

}
class AjaxExample extends ModelBase{
    private $id;
    private $group_id;
    private $_group;
    private $name;
    private $request_id;
    private $_request;
    private $response_id;
    private $_response;
    public function __construct($params){
        parent::construct();
        foreach ($params as $key => $value) {
            $this->$key = $value;
        }
        $this->_group = new AjaxGroup(array('id' => $this->group_id));
        $this->_request = new AjaxRequest(array('id' => $this->request_id));
        $this->_response = new AjaxResponse(array('id' => $this->response_id));
    }
    public function load(){
        $sql = "SELECT request_id, response_id, group_id, `name` FROM `example` WHERE `id`=:id";
        $sql = $this->fillSqlArgs($sql);
        $result = $this->queryRow($sql);
        foreach ($result as $name => $value) {
            $this->$name = $value;
        }
        $this->_group->load();
        $this->_request->load();
        $this->_response->load();
    }
    public function save(){
        $id = $this->id;
        if ($id === null){
            $sql = "INSERT INTO `example` (request_id, response_id, group_id, `name`) VALUES (:request_id, :response_id, :group_id, :name)";
        } else {
            $sql = "UPDATE `example` SET request_id=:request_id, response_id=:response_id, group_id=:group_id, name=:name WHERE id=:id";
        }
        $sql = $this->fillSqlArgs($sql);
        $this->execSql($sql);
        if ($id === null){
            $this->id = $id = $this->getLastInsertId();
        }
    }
}
class AjaxDb{
    private static $_sharedConnection;
    private $connection;
    public function __construct(){
        if (self::$_sharedConnection){
            $this->connection = self::$_sharedConnection;
            return;
        }
        self::$_sharedConnection = $this->connection = new mysqli('localhost', 'root', '', 'ajax');
    }
    public function __destruct(){
        if (self::$_sharedConnection){
            $this->close();
            self::$_sharedConnection = null;
        }
    }
    public function fillSqlArgs($sql, $args){
        // 先排序，这是为了防止出现这样的key被误替换：
        //          ':key' => 1, ':key-other'=>2
        //       误替换： ':key-other'结果被替换为了'1-other'
        $keys = array_keys($args);
        sort($keys, SORT_STRING);
        $keys = array_reverse($keys); // sort后的接口还是key在前，key-other在后，需要反转

        foreach ($keys as $key){
            $value = $args[$key];
            if ($key[0] != ':'){
                $key = ':'.$key;
            }
            if (is_numeric($value)){
                $sql = str_replace($key, strval(intval($value)) , $sql);
            }else {
                $sql = str_replace($key, "'" . mysqli_real_escape_string(strval($value)) . "'", $sql);
            }
        }
        return $sql;
    }
    public function execSql($sql){
        $result = $this->connection->query($sql);
        $result->close();
    }
    public function queryAll($sql){
        $result = $this->connection->query($sql);
        $data = $result->fetch_assoc();
        $result->close();
        return $data;
    }
    public function getLastInsertId(){
        return $this->connection->insert_id;
    }
    public function close(){
        $this->connection->close();
    }
}
class tools{
    public static function  leftOf($needle, $subject, $option){
        $pos = strpos($subject, $needle);
        if ($pos === false){
            return $option == 'FAIL_RETURN_ALL' ? $subject : null;
        } else{
            return substr($subject, 0, $pos);
        }
    }
    public static function rightOf($needl, $subject, $option){
        $pos = strpos($subject, $needle);
        if ($pos === false){
            return $option == 'FAIL_RETURN_ALL' ? $subject : null;
        } else{
            return substr($subject, $pos + 1);
        }
    }
}
$action = $_REQUEST['action'];
$action = $action ? $action : 'index';
try{
    $ajaxTool = new AjaxTool();
    $ajaxTool->run($action, $_REQUEST);
}catch(Exception $e){
    echo "<h1>Error: ".$e->getMessage()."</h1>\n";
    var_dump($e);
}

class AjaxView{
    public function renderIndex($params){
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ajax Tool</title>
    <style type="text/css">
        body{
            background-color: #d3d3d3;
        }
        .output{
            border-top: gray solid 1px;
            overflow: auto;
            max-width: 95vw;
            max-height: 80vh;
        }
        .raw{
            word-wrap: break-word;
        }
        h{
            font-weight: bold;
            font-size: 120%;
        }
        .json{
            white-space: pre;
        }
        textarea{
            width: 80vw;
        }
    </style>
</head>
<body>
<select id="method" >
    <option value="GET">GET</option>
    <option value="POST">POST</option>
</select>
<input type="text" id="url" placeholder="please input url.." style="width: 80em"
       onchange="javascript:split_url()" value="<?=$params['url']?>" />
<br/>
<label>With parameters:</label><br/>
<textarea id="param"  rows="15" onchange="auto_expand(this);"><?=$params['params']?></textarea> <br/>
<input type="button" value="SEND" onclick="javascript:send_request();" />
<input type="button" value="Split URL" onclick="javascript:split_url();" />
<input type="button" value="Decode PARAM" onclick="javascript:decode_param();" />
<input type="button" value="Encode PARAM" onclick="javascript:encode_param();" />

<h2>Ouput:</h2>
<div id="output" class="">
    <h>Pretty JSON:</h>
    <div id="json-output" class="json output"></div>
    <h>After base64 decode:</h>
    <div id="base64-output" class="base64 output"></div>
    <h>Raw:</h>
    <div id="raw-output" class="raw output"></div>
</div>
<hr/>
<h2>History:</h2>
<div id="log">

</div>
<script type="text/javascript" src="js/jquery.js" ></script>
<script type="text/javascript" src="js/json-formater.js"></script>
<script type="text/javascript" src="js/base64.js"></script>
<script type="text/javascript">

    function auto_expand(textArea){
        var value = $(textArea).val();
        var lines = value.split("\n") || [];
        if (lines.length > 10){
            $(textArea).css('height', lines.length * 1.14+'em');
        }
    }
    function split_url(){
        var url = $("#url").val();
        var a = url.split("?");
        if (a.length>1){
            $("#url").val(a[0]);
            $("#param").val(a[1]);
        }
    }
    function decode_param(){
        var param = $("#param").val().trim();
        if (param){
            var base64 = Base64.decode(param);
            base64 = base64 || param;
            var output = formatJson(base64);
            output = output || base64;
            $("#param").val(output);
        }
    }
    function encode_param(){
        var param = $("#param").val().trim();
        if (param){
            var jsonParam = parseJson(param);
            console.log("Param json object: %o", jsonParam);
            if (!!jsonParam){
                var base64 = Base64.encode(JSON.stringify(jsonParam));
            } else {
                var base64 = Base64.encode(param);
            }
            $("#param").val(base64);
        }
    }
    function parseJson(code, throws){
        try{
            code = ' function json_eval_function(){ return ' + code + ';}';
            eval(code);
            return json_eval_function();
        }catch(e){
            console.log(" Parse json failed: %o", e);
            if (throws){
                throw e;
            }
            return null;
        }
    }
    function send_request(){
        var ajaxOptions = {
            type: $("#method").val(),
            complete: function(){
                console.log("complete: %o", arguments);
            }
        };
        var url = $("#url").val();
        var param = $("#param").val().trim();
        if (param){
            var jsonParam = parseJson(param);
            console.log("Param json object: %o", jsonParam);
            if (!!jsonParam){
                param = Base64.encode(JSON.stringify(jsonParam));
            } else {
                param = param;
            }
            if ($("#method").val() == 'GET'){
                url = url + "?" + param;
                param = {};
            }else{
                url = url;
                ajaxOptions.data = param;
                ajaxOptions.dataType = 'text';
            }
            ajaxOptions.url = url;
        }
        console.log(" URL: " + url);
        console.log(" Ajax params: %o", ajaxOptions);
        clear_output();
        $.ajax(ajaxOptions).done(function(data){
            on_got_output(data);
            location.hash='#output';
        }).fail(function(jqXHR, error, msg){
            if (jqXHR && jqXHR.responseText){  // 有的时候根据返回的application/type解析会出错，但是实际上已经返回值了的。
                on_got_output(jqXHR.responseText);
                location.hash='#output';
                return;
            }
            console.log(" Ajax failed: %o, %o (%o)", msg, this, arguments);
            $("#raw-output").text(" Ajax failed: " + msg);
        });
    }
    function clear_output(){
        $("#raw-output, #base64-output, #json-output").empty();
    }

    function on_got_output(output){
        console.log("Raw output:: %o", {output: output});
        $("#raw-output").text(output);
        try{
            var base64 = Base64.decode(output);
            console.log("Base64 decoded: %o", {base64: base64});
            $("#base64-output").text(base64);
            try{
                var json = parseJson(base64);
                console.log("JSON: %o", {json: json});
                var jsonPretty = formatJson(base64);
                $("#json-output").text(jsonPretty);
            }catch(e){
                console.log("Failed to decode json: %o", e);
                console.log(e.stack);
                $("#json-output").text("Invalid JSON!");
            }
        }catch(e){
            console.log("Failed to decode base64: %o", e);
            console.log(e.stack);
            $("#base64-output").text("Invalid base64!");
        }
        log_it();
    }

    function log_it(){
        var output = $("#output").clone().removeAttr("id");
        $("*", output).removeAttr("id");
        output.prependTo("#log");
        $('<h2></h2>').text("Output").prependTo("#log");
        $('<div></div>').text($("#param").val()).css("white-space", 'pre').prependTo("#log");
        $('<div></div>').text($("#url").val()).prepend($("#method").val()+" ").prependTo("#log");
        $('<h2></h2>').text("Input").prependTo("#log");
        $("<i></i>").text("Time: "+new Date()).prependTo('#log');
        $("<hr/>").prependTo("#log");
        $("<hr/>").prependTo("#log");
    }
</script>
</body>
</html>
<?php }

    /**
     * 渲染结果，如果是字符串则直接输出，否则用json编码后输出
     * @param $result
     */
    public function renderResult($result){
        if (is_string($result)){
            echo $result;
        }else{
            echo json_encode($result);
        }
    }

    public function renderHistory(){

    }


}
?>
<!--- SQL ---
/*
SQLyog Ultimate v11.24 (32 bit)
MySQL - 5.5.16 : Database - ajax
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`ajax` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;

USE `ajax`;

/*Table structure for table `example` */

CREATE TABLE `example` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `request_id` int(11) NOT NULL,
  `response_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `group` */

CREATE TABLE `group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `history` */

CREATE TABLE `history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` int(10) unsigned NOT NULL,
  `response_id` int(10) unsigned DEFAULT NULL,
  `add_time` datetime NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `request` */

CREATE TABLE `request` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `url` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'URL（GET时不包括参数）',
  `params` longtext COLLATE utf8_unicode_ci NOT NULL,
  `method` tinyint(4) NOT NULL COMMENT '0: GET 1: POST 2: PUT...',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `response` */

CREATE TABLE `response` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `format` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT 'json, html, text',
  `cache` text COLLATE utf8_unicode_ci NOT NULL COMMENT '解析出的对象的缓存',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


--- SQL END--->