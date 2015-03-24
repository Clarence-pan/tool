<?php
/**
 * @var $request Request
 */

?>

<div>
    <h2><strong><?php echo $request->method ?></strong>&nbsp;<?php echo htmlspecialchars($request->url) ?></h2>
    <div>
        Group: <?php echo $request->group->name ?>
    </div>
    <?php foreach ($request->responses as $response) { ?>
        <?php $this->widget('ResponseBox', array('response' => $response)) ?>
    <?php } ?>
</div>
