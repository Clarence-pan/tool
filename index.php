<?php

$config = require(dirname(__FILE__).'/protected/config/main.php');
require(dirname(__FILE__).'/protected/main.php');
\tool\Main::run($config);


