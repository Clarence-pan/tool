<?php

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
    if ($category == 'application' and strpos($msgHead, '外部接口调用') === 0){
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