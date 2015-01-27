<?php
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