<?php
/**
 * @var $count int
 * @var $start int
 * @var $limit int
 */
$currentPage = intval($start / $limit); // 当前是第几页
$pageCount = intval(($count + $limit - 1) / $limit); // 页数
?>
<div style="width: 100%; text-align: center">
<?php
    $showPageCount = 10; // 显示几页
    $startPage = max(0, min($currentPage - intval($showPageCount / 2), $pageCount - $showPageCount));
    $endPage = $startPage + $showPageCount;
    if ($start > 0){
        echo CHtml::link('<<', with_get_params(array('', 'start' => 0, 'limit' => $limit)));
        echo CHtml::link(' < ', with_get_params(array('', 'start' => max($start - $limit, 0), 'limit' => $limit)));
    }
    for ($page = $startPage; $page < $pageCount && $page < $endPage; $page++){
        $options = ($page == $currentPage ? array('style' => 'color: white; background-color: black;') : array());
        $options['style'] .= 'margin-left: 0.5em; margin-right: 0.5em;';
        echo CHtml::link($page + 1, with_get_params(array('', 'start' => max($page * $limit, 0), 'limit' => $limit), $options));
    }
    if ($pageCount > $endPage){
        echo '..';
        echo CHtml::link($pageCount, with_get_params(array('', 'start' => ($pageCount - 1) * $limit, 'limit' => $limit )));
    }
    if ($currentPage < $pageCount - 1){
        echo CHtml::link(' > ', with_get_params(array('', 'start' => min($start + $limit, $count), 'limit' => $limit)));
        echo CHtml::link('>>', with_get_params(array('', 'start' => ($pageCount - 1) * $limit, 'limit' => $limit )));
    }
    echo "(Total: $count)";
?>
</div>