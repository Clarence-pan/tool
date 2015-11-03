<?php
/**
 * @var $this ViewController
 */

?>
<!DOCTYPE html>
<html>
<head>
    <title>Json View</title>
    <style type="text/css">
        #data{
            display: block;
            width: 100%;
            height: 50vh;
            min-height: 20em;
        }
        button{
            display: inline-block;
            width: 10em;
            height: 2em;
            line-height: 2em;
            text-align: center;
        }

    </style>
</head>
<body>
<form id="json-form" target="_blank" method="post" action="">
    <textarea id="data" name="data" placeholder="Please input json or php array..."></textarea>
    <input type="hidden" name="toPhpArray" value="0" />
    <input type="hidden" name="sorted" value="1" />
    <button id="go-to-view" type="submit">Go To View</button>
    <button id="copy-btn" type="button">Copy</button>
    <button id="select-all-btn" type="button">Select All</button>
    <button id="to-php-array" type="button">To Php Array</button>
    <button id="to-php-sorted-array" type="button">To Php Sorted Array</button>
</form>
<script type="text/javascript" src="/static/js/jquery.js"></script>
<script type="text/javascript" src="/static/zeroclipboard/ZeroClipboard.js"></script>
<script type="text/javascript" >
    function parseJson(code, throws){
        try{
            code = ' function json_eval_function(){ return ' + code + ';}';
            eval(code);
            return json_eval_function();
        }catch(e){
            console.log(" Parse json failed: %o", e);
            if (throws){
                throw e;
            }
            return null;
        }
    }

    function makePrettyJson(code){
        var obj = parseJson(code);
        if (!obj){
            return null;
        }

        return JSON.stringify(obj, null, '    ');
    }
    $(function(){
        $("#data").on('change', function(){
            var prettyJson = makePrettyJson($("#data").val());
            if (prettyJson){
                $("#data").val(prettyJson);
            }
        });

        $('#select-all-btn').on('click', function(){
            var data = $("#data")[0];
            data.setSelectionRange(0, data.textLength);
        });

        $('#to-php-array').on('click', function(){
            $('[name=toPhpArray]').val(1);
            $('#json-form').submit();
            $('[name=toPhpArray]').val(0);
        });

        $('#to-php-sorted-array').on('click', function(){
            $('[name=toPhpArray],[name=sorted]').val(1);
            $('#json-form').submit();
            $('[name=toPhpArray],[name=sorted]').val(0);
        });

        ZeroClipboard.config( { swfPath: "/static/zeroclipboard/ZeroClipboard.swf" } );
        var zclipboard = new ZeroClipboard($('#copy-btn'));
        zclipboard.on('copy', function(e){
            var c = e.clipboardData;
            c.setData('text/plain', $("#data").val());
        });

//       zclipboard.on('paste', function(e){
//           var c = e.clipboardData;
//           console.log('11111111111'+c.getData('text/plain'));
//           $("#data").val(c.getData('text/plain'));
//       });
    });
</script>
</body>