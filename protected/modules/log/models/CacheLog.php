<?php
namespace log\models;
class CacheLog implements ILog{
    public function __construct(){
        $this->cache = new \Memcache();
        $this->cache->connect('127.0.0.1', 11211);
        $this->count = intval($this->cache->get(self::LOG_ITEM_NUM));
    }

    public function count(){
        return $this->count;
    }

    public function seek($pos){
        $this->next = $pos;
    }
    public function next(){
        $next = $this->next;
        $this->next = $next + 1;
        if ($this->next > $this->count){
            return null;
        }
        $item = $this->cache->get(self::LOG_ITEM_PREFIX.$next);
        return $item;
    }
    public function eof(){
        return $this->next > $this->count;
    }
    public function clear(){
        $this->cache->set(self::LOG_ITEM_NUM, 0);
        $this->count = 0;
    }
    public function getCache($key){
        return $this->cache->get($key);
    }
    public function setCache($key, $value){
        return $this->cache->set($key, $value);
    }


    const LOG_ITEM_NUM = 'log-item-num';
    const LOG_ITEM_PREFIX = 'log-item-';

    private $cache;
    private $next = 0;
    private $count;
}