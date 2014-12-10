<?php
/**
 * Created by PhpStorm.
 * User: clarence
 * Date: 14-12-9
 * Time: 上午2:40
 */

?>
<!doctype html>
<html>
<head>
    <style type="text/css">
        body{
            width: auto;
            height: 100%;
            margin: 0;
            padding: 0;
        }
        .header, .footer, .body{
            width: auto;
            margin: 0;
            padding: 1em 4em;
            align-self: center;
            background: silver;
        }
        .body{
            min-height: 70vh;
        }
    </style>
    <title>Ajax Tool</title>
</head>
<body>
    <div class="header" align="center">
        <h1>Ajax Tool</h1>
    </div>
    <div class="body" align="center">
        <?php echo $content ?>
    </div>
    <div class="footer" align="center">
        <hr/>
        Copyright Tool&copy; 2014-2015.
    </div>
</body>
</html>