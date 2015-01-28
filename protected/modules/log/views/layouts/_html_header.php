<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <script type='text/javascript' src='/static/js/jquery.js'></script>
    <script type="text/javascript">
        <?php echo file_get_contents(__DIR__.'/log.js') ?>
    </script>
    <title></title>
    <style type="text/css" >
        <?php echo file_get_contents(__DIR__.'/log.css') ?>
        li.stackTrace{
        <?PHP if (!@$_REQUEST['displayStackTrace']): ?>
            display: none ;
        <?PHP else: ?>
            display: block !important;
            position: relative !important;
            left: 11em !important;
        <?PHP endif; ?>
            white-space: pre-wrap;
        }
    </style>
</head>
<body>