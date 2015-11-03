<?php

session_start();
print_r(array(
    'GET' => $_GET,
    'POST' => $_POST,
    'COOKIE' => $_COOKIE,
    'SERVER' => $_SERVER,
    'SESSION' => $_SESSION
));

$_SESSION['test'] = 'test-session-data';
session_write_close();

setcookie('test', 'test-cookie-data');