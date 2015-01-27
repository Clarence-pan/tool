<?php
namespace tool\util\model\log;
class CacheLog implements ILog{
    const LOG_ITEM_NUM = 'log-item-num';
    const LOG_ITEM_PREFIX = 'log-item-';

    public $fileSize;
    public $filemtime;
    public $fileName;

    private $cache;
    public function __construct(){
        $this->cache = new \Memcache();
        $this->cache->connect('127.0.0.1', 11211);
        $fileSize = $this->cache->get(self::LOG_ITEM_NUM);
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
        $item = $this->cache->get(self::LOG_ITEM_PREFIX.$next);
        return $item;
    }
    public function clear(){
        $this->cache->set(self::LOG_ITEM_NUM, 0);
        $this->fileSize = 0;
    }
    public function getCache($key){
        return $this->cache->get($key);
    }
    public function setCache($key, $value){
        return $this->cache->set($key, $value);
    }
}