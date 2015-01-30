<?php
/**
 * @var $count int
 * @var $start int
 * @var $limit int
 */
$currentPage = intval($start / $limit); // 当前是第几页
$pageCount = intval(($count + $limit - 1) / $limit); // 页数
?>
<div style="width: 100%; text-align: right">
<?php
    $showPageCount = 10; // 显示几页
    $startPage = max(0, min($currentPage - intval($showPageCount / 2), $pageCount - $showPageCount));
    $endPage = $startPage + $showPageCount;
    if ($start > 0){
        echo CHtml::link('<<', array('', 'start' => 0, 'limit' => $limit));
        echo CHtml::link(' < ', array('', 'start' => max($start - $limit, 0), 'limit' => $limit));
    }
    for ($page = $startPage; $page < $pageCount && $page < $endPage; $page++){
        echo CHtml::link(" $page ", array('', 'start' => max($page * $limit, 0), 'limit' => $limit));
    }
    if ($start + $limit < $count){
        echo CHtml::link(' > ', array('', 'start' => min($start + $limit, $count), 'limit' => $limit));
        echo CHtml::link('>>', array('', 'start' => floor($count / $limit) * $limit, 'limit' => $limit ));
    }
    echo "[$currentPage/$pageCount(Total: $count)]";
?>
</div>