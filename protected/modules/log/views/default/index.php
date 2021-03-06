<?php
/**
 * @var $this DefaultController The controller
 * @var $filter string  Log filter name
 * @var $start int  -- From where to display log
 * @var $limit int  -- How many log items to display
 * @var $summaryOnly bool - whether only display summary
 */

/**
 * @param array $params
 * @return array
 */
function with_get_params(array $params){
    $index = array_shift($params);
    return array_merge(array($index), $_GET, $params);
}

function get_style_of_request($request) {
    $colors = explode(' ', '#ffffff #ffddff #ddffff #ffffdd #ddddff #ddffdd #ffdddd #dddddd');
    static $requests = array();
    if (!array_key_exists($request, $requests)) {
        $i = count($requests);
        $requests[$request] = $i;
    } else {
        $i = $requests[$request];
    }
    $i = $i % count($colors);

    return 'background-color: ' . $colors[$i];
}
function output_logs(log\models\ILog $log, $id, $limit, $interested, $summaryOnly) {
    for ($item = $log->next(), $i = 0; !$log->eof() and (!$limit or $i < $limit); $item = $log->next(), $id++, $i++):
        $item['line'] = $id + 1;
        if (filterLog($item, $interested)) {
            continue;
        }
        if ($summaryOnly){
            continue;
        }
        ?>
        <ul class="log <?php echo $item['class']?>" style="<?php echo get_style_of_request($item['request']) ?>" id="line-<?php echo $item['line'] ?>">
            <li class="line"><?php echo $item['line']  ?>:</li>
            <?php foreach ($item as $key => $logValue):?>
                <?php
                    if (in_array($key, array('line', 'class'))){
                        continue;
                    }
                ?>
                <li class="<?php echo $key ?>"
                    <?php if ($key == 'msgBody') { ?>
                        ondblclick="showInNewWindow(this);"
                        oncontextmenu="expandThisCell(this, arguments[0]);return false;"
                        title="右击展开，双击在新的窗口查看"
                    <?php } else if ($key == 'msgHead' || $key == 'category' || $key == 'level') { ?>
                        ondblclick="showStackTrace(this);"
                    <?php } ?>
                    ><?php echo htmlspecialchars($logValue) ?></li>
            <?php endforeach; ?>
        </ul>
        <?PHP if ($id % 200 == 0): ?>
        <script type="application/javascript">
            scrollToBottom();
        </script>
    <?PHP endif;
    endfor;

    if ($_GET['sum']){
        output_summary();
    }
    return $id;
}

$this->renderPartial("/_filterLog");

$log = new log\models\CacheLog();

if (!@$_REQUEST["autoAppend"]) {
    $this->renderPartial('/_html_header');
    if ($filter != 'basic') {
        $links = array('View All' => with_get_params(array('index')));
    } else {
        $links = array('interested' => with_get_params(array('interested')));
    }
    $links = array_merge($links, array(
        'cache' => array('cache'),
        'summary' => array('summary'),
        '?' => array('help'),
    ));
    $this->renderPartial('/_tools', array('links' => $links, 'pageEndLogPos' => $log->count(), 'pager' => array('start' => $start, 'limit' => $limit)));
}

if (@$_REQUEST['clear']) {
    if (@$_REQUEST['clear'] >= $log->count() || @$_REQUEST['clear'] == 'all') {
        $log->clear();
        echo "Clear finished!";
        echo "<script>window.history.pushState({}, 'Log Cleared', '/log#log-cleared');</script>";
        return;
    } else {
        echo "Already cleared! The following is new one: ";
    }
}
if ($start) {
    $log->seek($start);
}
$pager = $this->renderPartial('pager', array('count' => $log->count(), 'start' => $start, 'limit' => $limit), true);
echo $pager;
output_logs($log, $start, $limit, $filter == 'interested', $summaryOnly);
if ($log->count()){
    echo $pager;
}

echo '<script type="application/javascript">
                scrollToBottom();
          </script>';


