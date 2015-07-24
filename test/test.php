<?php
var_dump($GLOBALS);

parse_str($_GET['params'], $params);
print_r($params);
// =>
//Array
//(
//    [a] => 1
//    [b] => 2
//)

?>
<script type="text/javascript" src="//ssl1.tuniucdn.com/j/201406191714/3rd/jquery-1.7.2.min.js,common/in-min.js"></script>
<form id="slider_settings_form" >
    <input type="text" name="a" value="1">
    <input type="submit">
    <script>
        var ajaxurl = '/test/test.php';
        $("#slider_settings_form").submit(function(e) {
            var postData = $(this).serialize();
            $.ajax({
                url: ajaxurl,
                data: {
                    "params": postData,
                    "action": "saveFormSettings"
                },
                method: "POST",
                success: function(response) {
                    alert(response);
                },
            });
            return false;
        });
    </script>
</form>