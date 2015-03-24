<?php
/**
 * @var $this DefaultController
 * @var $groups array
 */


foreach ($groups as $group) {
    $this->widget('application.modules.ajax.widgets.GroupBoxWidget', array('group' => $group));
}




