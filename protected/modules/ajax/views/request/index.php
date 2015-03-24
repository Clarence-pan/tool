<?php
 /**
  * @var $requests array
  *
  */

?>

<ul>
    <?php foreach ($requests as $request) { ?>
        <li><?php echo $this->widget('application.modules.ajax.widgets.RequestSummaryBoxWidget', array('request' => $request)) ?></li>
    <?php } ?>
</ul>