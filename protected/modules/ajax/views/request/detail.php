<?php
/**
 * @var $request Request
 * @var $response Response
 */

?>

<div>
    <h2><strong><?php echo $request->method ?></strong>&nbsp;<?php echo htmlspecialchars($request->url) ?></h2>
    <div>
        Group: <?php echo $request->group->name ?>
    </div>
    <?php
    if ($response != null){
        $this->widget('application.modules.ajax.widgets.ResponseBoxWidget', array('response' => $response));
    } else if (empty($request->responses)) {
        echo "<strong>No response yet. </strong>";
    } else {
        foreach ($request->responses as $response) {
            $this->widget('application.modules.ajax.widgets.ResponseBoxWidget', array('response' => $response));
        }
    }
    ?>
    <form target="_self" action="<?php echo CHtml::normalizeUrl(array('request/query', 'id' => $request->id)) ?>" method="POST">
        <input type="submit" value="-- Click To Query Response --">
    </form>
</div>
