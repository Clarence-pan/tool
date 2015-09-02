<?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        var_dump(array(
            'GET' => $_GET,
            'POST' => $_POST,
            'FILES' => $_FILES,
        ));

        foreach ($_FILES as $file) {
            echo "file ", $file['tmp_name'], PHP_EOL;
            echo "   size is ", filesize($file['tmp_name']), PHP_EOL;
        }

        return;
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="HandheldFriendly" content="true">
    <meta name="MobileOptimized" content="width">
    <meta name="viewport" id="viewport" content="width=device-width,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="applicable-device"content="mobile">
    <style type="text/css">
        img{
            width: 320px;
        }
        #result div{
            margin: 2em;
            border: 1px solid darkgray;
            white-space: pre;
        }
    </style>
</head>
<body>
    <ul id="images"></ul>
    <input type="file" id="addImg" value="add images" accept="image/*" multiple="multiple" />
    <input type="submit" id="submit" value="submit">
    <div id="result"></div>
    <script>
        (function (){
            var images = [];
            var $addImg = document.getElementById('addImg');
            var $images = document.getElementById('images');
            var $submit = document.getElementById('submit');
            var $result = document.getElementById('result');

            $addImg.addEventListener('change', function(){
                for (var i = 0; i < $addImg.files.length; i++){
                    (function(file){
                        var reader = new FileReader();
                        reader.onload = function(){
                            var $img = document.createElement('img');
                            $img.src = reader.result;
                            var $li = document.createElement('li');
                            $li.appendChild($img);
                            $images.appendChild($li);
                            images.push(file);
                        };
                        reader.readAsDataURL(file);
                    })($addImg.files[i]);
                }
            });

            $submit.addEventListener('click', function(){
                var data = new FormData();
                for (var i = 0; i < images.length; i++){
                    data.append('images' + i, images[i]);
                }
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function(){
                    console.log('ready state change: ' + xhr.readyState + ' -- ' + xhr.status + ' (' + xhr.statusText + ")");
                    if (xhr.readyState == 4){ // done.
                        var $div = document.createElement('div');
                        $div.innerHTML = xhr.responseText;
                        $result.appendChild($div);
                    }
                };
                xhr.open('POST', location.href);
                xhr.send(data);
            });
        })();
    </script>
</body>
</html>


