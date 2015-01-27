<?php
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