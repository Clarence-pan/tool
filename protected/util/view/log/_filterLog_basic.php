<?php

function filterLog($logLine){
    if (!@$_REQUEST['noFilterDbProfile']
        && $logLine['level'] == 'profile'
        && in_array($logLine['category'], array('system.db.CDbCommand.query', 'system.db.CDbCommand.execute'))
        && in_array($logLine['msgHead'], array('begin', 'end'))){
        return true;
    }
    return true;
}