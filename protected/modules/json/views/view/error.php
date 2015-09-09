<?php
/**
 * @var $error
 * @var $text
 */
?>
<html>
<head>
    <style>
        .text{
            display: block;
            white-space: pre-line;
            margin: 1rem;
            padding: 1rem;
            border: none;
        }
        .error{
            color: #c12e2a;
            margin: 0 1rem;
            padding: 0 1rem;
            font-size: 80%;
        }
    </style>
    <title>Not JSON: <?php echo $error['message'] ?></title>
</head>
<body>
    <div class="error">Not JSON: <?php echo $error['message'] ?></div>
    <div class="text"><?php echo $text ?></div>
</body>
</html>
