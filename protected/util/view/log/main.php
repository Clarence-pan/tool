<?php
/**
 * @var $this \tool\util\controller\LogController The controller
 * @var $content string The content of layout.
 * @var $filter string  Log filter name
 */
function get_style_of_request($request){
    $colors = explode(' ', '#ffffff #ffddff #ddffff #ffffdd #ddddff #ddffdd #ffdddd #dddddd');
    static $requests = array();
    if (!array_key_exists($request, $requests)){
        $i = count($requests);
        $requests[$request] = $i;
    }else{
        $i = $requests[$request];
    }
    $i = $i % count($colors);
    return 'background-color: '.$colors[$i];
}

function output_logs($log, $id=100000){
    for ($logline = $log->next(); $logline; $logline = $log->next(), $id++): /* id="<?php echo $id?>" */
        if (filterLog($logline)){
            continue;
        }
        ?>
        <ul  class="log" style="<?php echo get_style_of_request($logline['request'])?>">
            <li class="line"><?php echo  $id + 1 ?>:</li>
            <?php foreach ($logline as $key => $logValue): ?>
                <li class="<?php echo  $key ?>"
                    <?php if ($key == 'msgBody'){ ?>
                        ondblclick="javascript:showInNewWindow(this);"
                        onclick="javascript:expandThisCell(this)"
                    <?php }else if ($key == 'msgHead' || $key == 'category' || $key == 'level'){ ?>
                        ondblclick="javascript:showStackTrace(this);"
                    <?php } ?>
                    ><?php echo  htmlspecialchars($logValue) ?></li>
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
require(__DIR__."/_filterLog_$filter.php");

if(!@$_REQUEST["autoAppend"]){
    $this->renderPartial('log/_html_header');
    if ($filter != 'basic'){
        $links =  array('view' => 'View All');
    } else {
        $links = array('interested' => 'interested');
    }
    $this->renderPartial('log/_tools.php', array('links' => $links));
}

$log = new \tool\util\model\log\CacheLog();
if (@$_REQUEST['clear']){
    if (@$_REQUEST['clear'] >= $log->fileSize || @$_REQUEST['clear'] == 'all') {
        $log->clear();
        echo "Clear finished!";
        return;
    }else{
        echo "Already cleared! The following is new one: ";
    }
}


if (@$_REQUEST['seek']){
    $log->seek(intval(@$_REQUEST['seek']));
}
$id =  @$_REQUEST["id"];
$id = $id ? $id : 0;
$id = output_logs($log, $id);



