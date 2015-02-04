<?php
/**
 * @var $this DefaultController The controller
 * @var $filter string  Log filter name
 * @var $start int  -- From where to display log
 * @var $limit int  -- How many log items to display
 * @return mixed
 */
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
function output_logs(log\models\ILog $log, $id, $limit) {
    for ($item = $log->next(), $i = 0; !$log->eof() && $i < $limit; $item = $log->next(), $id++, $i++):
        $item['line'] = $id + 1;
        if (filterLog($item)) {
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

    return $id;
}

$filter = ((isset($filter) and $filter) ? $filter : 'basic');
$this->renderPartial("/_filterLog_$filter");

$log = new log\models\CacheLog();

if (!@$_REQUEST["autoAppend"]) {
    $this->renderPartial('/_html_header');
    if ($filter != 'basic') {
        $links = array('View All' => array('index'));
    } else {
        $links = array('interested' => array('interested'));
    }
    $links['cache'] = array('cache');
    $links['?'] = array('help');
    $this->renderPartial('/_tools', array('links' => $links, 'pageEndLogPos' => $log->count(), 'pager' => array('start' => $start, 'limit' => $limit)));
}

if (@$_REQUEST['clear']) {
    if (@$_REQUEST['clear'] >= $log->count() || @$_REQUEST['clear'] == 'all') {
        $log->clear();
        echo "Clear finished!";

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
output_logs($log, $start, $limit);
echo $pager;

echo '<script type="application/javascript">
                scrollToBottom();
          </script>';


