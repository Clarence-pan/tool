<!doctype html>
<?php session_start(); ?>
<html>
<head>
    <meta content="text/html; charset=utf-8" http-equiv="content-type"  />
    <link rel="stylesheet" type="text/css" href="/static/css/main.css" />
    <link rel="stylesheet" type="text/css" href="/static/css/bootstrap.css" />
    <script src="/static/js/jquery.js" ></script>
    <script src="/static/js/bootstrap.js"></script>
    <title><?php echo $title ?></title>
</head>
<body>
<div class="container">
<div class="panel">
<div class="panel-heading" align="center">

</div>
<div class="panel-body" align="center">
    <div class="body-content" align="left">
        <?php echo $content ?>
    </div>
</div>
<div class="panel-footer">

</div>
</div>
</div>
</body>
</html>