<?php
/**
 * @var $this GroupBoxWidget
 */
?>

<div class="group-box">
    <h2><?php echo htmlspecialchars($this->group->name) ?></h2>
    <ul>
    <?php foreach ($this->requests as $request) { ?>
        <li><?php $this->widget('application.modules.ajax.widgets.RequestSummaryBoxWidget', array('request' => $request)) ?></li>
    <?php } ?>
    </ul>
</div>