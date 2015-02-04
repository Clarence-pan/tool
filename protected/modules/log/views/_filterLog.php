<?php

$requests = array();
$prevRequest = '';
$summaryId = 0;
function filterLog(&$logItem, $interested){
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
            output_summary();
        }
        $prevRequest = $request;
    }

    if (!$level == 'profile'
        && in_array($category, array('system.db.CDbCommand.query', 'system.db.CDbCommand.execute'))
        && in_array($msgHead, array('begin', 'end'))){
        return true;
    }

    if ($_GET['request']){
        if (strpos($request, $_GET['request']) === false){
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

    if (!$interested){
        return false;
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

/**
 * 输出总结
 */
function output_summary() {
    global $prevRequest;
    global $requests;
    global $summaryId;

    $summary = get_summary_of_request($requests[$prevRequest]);
    $style = get_style_of_request($prevRequest);
    $summaryId++;
    $link = CHtml::link('>>', with_get_params(array('index', 'sum' => false, 'request'=>$prevRequest, 'start'=>$_GET['start'], 'limit'=>$_GET['limit'])));
    echo "<div class='summary-container' style='$style' id=\"summary-$summaryId\"><h3>$prevRequest $link</h3><div class='summary'>$summary</div></div>";
    echo "<script type='text/javascript'>$(function(){ $('#summary-{$requests[$prevRequest]['summary-id']}').remove() });</script>";
    $requests[$prevRequest]['summary-id'] = $summaryId;

    return array($summaryId, $requests);
}

function get_summary_of_request($request){
    $r = '<dl class="summary">';
    foreach (array('外部接口调用', 'Querying SQL', 'Executing SQL', 'memcache') as $name) {
        $sum = $request[$name];
        if ($sum['count']){
            $error = ($sum['count'] > 5 ? 'error' : '');
            $r .= "<dt class='summary $error' onclick='toggle_detail(this)'>$name {$sum['count']} 次</dt>";
            foreach ($sum['rows'] as $line => $row) {
                $row = htmlspecialchars($row);
                if ($name == 'Querying SQL'){
                    $dupClass = get_duplicated_class($row, $sum['rows']);
                }
                if (strstr($_SERVER['REQUEST_URI'], 'summary')){
                    $url = "index?start=".($_GET['start'] + $line - 1)."&limit=1";
                    $target = '_blank';
                }
                $r .= "<dd class='detail $dupClass'><a href=\"$url#line-$line\" target='$target'>$line</a>: $row</dd>";
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