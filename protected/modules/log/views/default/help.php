<?php
/**
 * @var $this DefaultController
 */
?>
<html>
<head>
    <style type="text/css">
        <?php echo file_get_contents(__DIR__.'/../log.css') ?>
        form {
            display: block;
            min-width: 50em;
            width: 50vw;
            margin-left: auto;
            margin-right: auto;
        }

        form label {
            display: inline-block;
        }

        form input[type="number"] {
            width: 5em;
        }

        form input[type="text"] {
            width: 20em;
        }

        form input[type="submit"]{
            width: 10em;
            text-align: center;
        }

    </style>
</head>
<body>
<form id="query" action="index" method="GET">
    <h3>Index</h3>
    <label>
        <input type="checkbox" name="sum"/>
        Summary - whether display summary of requests (not perfect!)
    </label>
    <br/>
    <label>
        <input type="checkbox" name="profile"/>
        Profile - whether display profile results
    </label>
    <br/>
    <label>
        Matching @msgHead at index 0:
        <input type="text" name="heading"/>
    </label>
    <br/>
    <label>
        Substring matching @category:
        <input type="text" name="category"/>
    </label>
    <br/>
    <label>
        Substring matching @request URL:
        <input type="text" name="request"/>
    </label>
    <br/>
    <label>
        Start from <input type="number" name="start" value="0"/>
    </label>
    <label>
        limit <input type="number" name="limit" value="500"/> items.
    </label>
    <input type="submit" value="submit">
</form>
<form action="summary" method="GET" id="query-summary">
    <h3>Summary</h3>
    <label>
        <input type="checkbox" name="withoutDetail"/>
        Without detailed summary
    </label>
    <input type="submit" value="submit"/>
</form>

<form action="disableTraceStack" method="post" target="_blank">
    <input type="submit" value="Disable trace stack" />
</form>
<form action="enableTraceStack" method="post" target="_blank">
    <input type="submit" value="Enable trace stack" />
</form>

</body>
</html>