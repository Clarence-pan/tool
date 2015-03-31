<?php
/**
 * @var $this DefaultController
 * @var $groups array
 */
?>

<iframe name="leftFrame" id="leftFrame">
<?php

foreach ($groups as $group) {
    $this->widget('application.modules.ajax.widgets.GroupBoxWidget', array('group' => $group));
}


?>

</iframe>
<iframe name="rightFrame" id="rightFrame"></iframe>