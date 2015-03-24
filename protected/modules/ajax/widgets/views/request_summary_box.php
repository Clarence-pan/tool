<?php
/**
 * @var $this RequestSummaryBoxWidget
 */
?>
<div class="request-summary-box box">
    <?php echo CHtml::link($this->request->url, array('request/detail', 'id' => $this->request->id)) ?>
</div>