<?php

$config = require(__DIR__.'/main.php');

$config['components']['log']['routes'][] = array(
    'class' => 'CWebLogRoute'
);

return $config;