<?php

$config = require(__DIR__.'/main.php');
unset($config['modules']['gii']);
unset($config['modules']['components']['log']);
return $config;