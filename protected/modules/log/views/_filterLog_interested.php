<?php

$requests = array();
$prevRequest = '';
function filterLog(&$logItem){
    global $prevRequest;
    global $requests;
    /**
     * @var $category string
     * @var $msgHead string
     * @var $msgBody string
     * @var $request string
     * @var $level string
     * @var $line int
     */
    extract($logItem);

    if ($request != $prevRequest && $_GET['sum']){
        if ($prevRequest){
            $summary = get_summary_of_request($requests[$prevRequest]);
            $style = get_style_of_request($prevRequest);
            echo "<div style='$style'><h3>Summary Of $prevRequest</h3><div class='summary'>$summary</div></div>";
        }
        $prevRequest = $request;
    }

    if (!$level == 'profile'
        && in_array($category, array('system.db.CDbCommand.query', 'system.db.CDbCommand.execute'))
        && in_array($msgHead, array('begin', 'end'))){
        return true;
    }

    if ($_GET['category'] && $_GET['heading']){
        if (strpos($_GET['category'], $category) === false or strpos($msgHead, $_GET['heading']) !== 0){
            return true;
        }
    }
    if ($_GET['category']){
        if (strpos($_GET['category'], $category) === false){
            return true;
        }
    }
    if ($_GET['heading']){
        if (strpos($msgHead, $_GET['heading']) !== 0){
            return true;
        }
    }

    if ($category == 'CWebApplication' ){
        return false;
    }
    if ($category == 'application' and strpos($msgHead, '外部接口调用') === 0){
        $requests[$request]['外部接口调用']['count']++;
        $requests[$request]['外部接口调用']['rows'][$line] = $msgHead;
        return false;
    }
    if ($category == 'memcache' and strpos($msgHead, 'Memcache') === 0){
        $requests[$request]['memcache']['count']++;
        $requests[$request]['memcache']['rows'][$line] = $msgHead;
        return false;
    }
    if ($category == 'system.db.CDbCommand'){
        $sql = $msgBody;
        if ($requests[$request]['SQLs'][$sql]){
            $logItem['time'] = 'DUPLICATED-SQL! #'.$requests[$request]['SQLs'][$sql] .'  '. $logItem['time'];
            $logItem['class'] = 'error';
        } else {
            $requests[$request]['SQLs'][$sql] = $logItem['line'];
        }
    }
    if ($category == 'system.db.CDbCommand' and strpos($msgHead, 'Querying SQL') === 0){
        $requests[$request]['Querying SQL']['count']++;
        $requests[$request]['Querying SQL']['rows'][$line] = $msgBody;
        return false;
    }
    if ($category == 'system.db.CDbCommand' and strpos($msgHead, 'Executing SQL') === 0){
        $requests[$request]['Executing SQL']['count']++;
        $requests[$request]['Executing SQL']['rows'][$line] = $msgBody;
        return false;
    }
    return true;
}

function get_summary_of_request($request){
    $r = '<dl class="summary">';
    foreach (array('外部接口调用', 'Querying SQL', 'Executing SQL', 'memcache') as $name) {
        $sum = $request[$name];
        if ($sum['count']){
            $r .= "<dt class='summary'>$name</dt>";
            $r .= "<dd class=\"count\">count: ".$sum['count']."</dd>";
            $r .= "<dt class='detail'>Detail: </dt>";
            foreach ($sum['rows'] as $line => $row) {
                $row = htmlspecialchars($row);
                if ($name == 'Querying SQL'){
                    $dupClass = get_duplicated_class($row, $sum['rows']);
                }
                $r .= "<dd class='detail $dupClass'><a href=\"#line-$line\">$line</a>: $row</dd>";
            }

        }
    }
    $r .= '</dl>';
    return $r;
}

function get_item_counts($collection){
    $counts = array();
    foreach ($collection as $item) {
        $counts[$item]++;
    }
    return $counts;
}

/**
 * 1. 重复次数越多，颜色越深
 * 2. 不同的item，对应不同的颜色
 */
function get_duplicated_class($item, $collection){
    $counts = get_item_counts($collection);
//    var_dump($counts);
    $count = $counts[$item];
    if ($count <= 1){
        return '';
    } else if ($count < 5){
        return 'duplicated-'.$count;
    } else {
        return 'duplicated-too-many-times';
    }
}