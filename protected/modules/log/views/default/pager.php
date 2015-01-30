<?php
/**
 * @var $count int
 * @var $start int
 * @var $limit int
 */
$pageCount = ($count - $start);
if ($pageCount > $limit){
    $pageCount = $limit;
}
?>
<div style="width: 100%; text-align: right">
<?php
    echo CHtml::link('<<', array('', 'start' => 0, 'limit' => $limit));
    if ($start > 0){
        echo CHtml::link(' < ', array('', 'start' => max($start - $limit, 0), 'limit' => $limit));
    }
    if ($start + $limit < $count){
        echo CHtml::link(' > ', array('', 'start' => min($start + $limit, $count), 'limit' => $limit));
    }
    echo CHtml::link('>>', array('', 'start' => floor($count / $limit) * $limit, 'limit' => $limit ));
    echo "[$start..$pageCount/$count]";
?>
</div>