<?php

$requests = array();

function filterLog(&$logItem){
    if (!@$_REQUEST['noFilterDbProfile']
        && $logItem['level'] == 'profile'
        && in_array($logItem['category'], array('system.db.CDbCommand.query', 'system.db.CDbCommand.execute'))
        && in_array($logItem['msgHead'], array('begin', 'end'))){
        return true;
    }
    /**
     * @var $category string
     * @var $msgHead string
     * @var $msgBody string
     * @var $request string
     */
    extract($logItem);
    if ($category == 'CWebApplication' ){
        return false;
    }
    if ($category == 'application' and strpos($msgHead, '外部接口调用') === 0){
        return false;
    }
    if ($category == 'memcache' and strpos($msgHead, 'Memcache') === 0){
        return false;
    }
    if ($category == 'system.db.CDbCommand'){
        global $requests;
        $sql = $msgBody;
        if ($requests[$request]['SQLs'][$sql]){
            $logItem['time'] = 'DUPLICATED-SQL! #'.$requests[$request]['SQLs'][$sql] .'  '. $logItem['time'];
            $logItem['class'] = 'error';
        } else {
            $requests[$request]['SQLs'][$sql] = $logItem['line'];
        }
    }
    if ($category == 'system.db.CDbCommand' and strpos($msgHead, 'Querying SQL') === 0){
        return false;
    }
    if ($category == 'system.db.CDbCommand' and strpos($msgHead, 'Executing SQL') === 0){
        return false;
    }
    return true;
}