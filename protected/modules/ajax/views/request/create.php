<?php
/**
 * @var $request Request
 * @var $groupList array
 * @var $executeQuery bool
 * @var $saveSuccess bool
 * @var $response Response
 */
?>
<form target="_self" method="POST" action="<?php echo CHtml::normalizeUrl('create') ?>">
    <div>
        <?php echo CHtml::listBox('method', $request->method, array('GET' => 'GET', 'POST' => 'POST'), array('size' => 1)) ?>
        <input type="text" name="url" style="width: 60em" value="<?php echo $request->url ?>" placeholder="http://enter.your.url/here" />
        <label>
            Group:
            <?php echo CHtml::listBox('groupId', $request->groupId, CHtml::listData($groupList, 'id', 'name'), array('size' => 1)) ?>
        </label>
        <label>
            Format:
            <?php
            echo CHtml::listBox('paramsFormat', $request->paramsFormat,
                                array(
                                    Request::PARAM_FORMAT_RAW => Request::PARAM_FORMAT_RAW,
                                    Request::PARAM_FORMAT_BASE64JSON => Request::PARAM_FORMAT_BASE64JSON
                                ),
                                array('size' => 1)) ?>
        </label>
    </div>
    <textarea name="params" rows="10" cols="140" placeholder="Enter params here..."><?php echo $request->params ?></textarea>
    <div>
        <input type="submit" value="DO REQUEST">
    </div>
</form>



<?php

if ($executeQuery){
    if ($saveSuccess){
        ?> <div class="info">Request is saved!</div> <?php
    } else {
        ?> <div class="error">Failed to save request!</div> <?php
    }

    if ($response){
        $this->widget('application.modules.ajax.widgets.ResponseBoxWidget', array('response' => $response));
    } else {
        ?> <div class="error">No response!</div> <?php
    }
}
