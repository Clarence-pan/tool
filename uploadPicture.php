<?PHP

function uploadPicture($fileName, $filePath){
    preg_match('/\.[^\.]+/', $fileName, $match);
    $fileName = md5(rand()*10000000) . $match[0];
    $path = $_SERVER['TMP'];
    move_uploaded_file($filePath, $path. $fileName);
    $filename_code = base64_encode($fileName);
    $fileData = array();
    $fileData['file'] = '@'.$path. $fileName;
    $domain = 'public-api.bj.pla.tuniu.org';
    $url = $domain.
        "/filebroker/upload?".
        "name=$filename_code&sub_system=".($_REQUEST['subSystem'] ? $_REQUEST['subSystem'] : 'vnd').
        "&folder=".($_REQUEST['folder'] ? $_REQUEST['folder'] : 'cdn')."&sync_quick=1&sync_place=0";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, 1 );
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $fileData);
    curl_setopt($curl, CURLOPT_USERAGENT,"Mozilla/4.0");
    $result = curl_exec($curl);
    if ($_REQUEST['showRawResult']){
        echo "原始结果：<br/>".$result . "<br/>";
    }

    //删除临时图片
    $rs = json_decode($result,true);
    if ($rs['success']){
        unlink($path. $fileName);
    }

    // 找到特定域名的图片，就用那个图片
    $resultDomain = $domain;
    $resultUrl = '';
    foreach ($rs['data'] as $rsi) {
        $resultUrl = $rsi['url'];
        if (strstr($rsi['url'], $resultDomain)){
            break;
        }
    }

    $resolution = $_REQUEST['resolution'];
    $resolution = $resolution ? $resolution : 'w768_h1024';

    $resultUrl = str_replace('public-api.bj.pla.tuniu.org', 'm.tuniucdn.com', $resultUrl);
    $urlParts = explode('/', $resultUrl);
    $urlParts[count($urlParts) - 1] = str_replace('.', '_'.$resolution.'_c0_t0.', $urlParts[count($urlParts) - 1]);
    $resultUrl = implode('/', $urlParts);
    return $resultUrl;
}


$file = $_FILES['upload_file'];
if ($file){
    $url = uploadPicture($file['name'], $file["tmp_name"]);

    if ($url){
        echo "<b>".$file['name']."</b>上传成功！<br/>";
        echo "URL: " . $url;
        echo "<br/>";
        echo '<a href="'.$url.'">点击查看图片</a>';
        ?>
        <script type="text/javascript">
            window.open("<?=$url?>");
        </script>
        <?PHP
    } else {
        echo "上传失败！";
        return;
    }

    die;
}

$resolutions = array(
    array("width" =>60, "height" =>60),
    array("width" =>80, "height" =>80),
    array("width" =>120, "height" =>120),
    array("width" =>370, "height" =>255),
    array("width" =>800, "height" =>400),
    array("width" =>90, "height" =>60),
    array("width" =>200, "height" =>237),
    array("width" =>200, "height" =>113),
    array("width" =>240, "height" =>135),
    array("width" =>138, "height" =>80),
    array("width" =>180, "height" =>100),
    array("width" =>120, "height" =>170),
    array("width" =>247, "height" =>271),
    array("width" =>240, "height" =>135),
    array("width" =>20, "height" =>20),
    array("width" =>125, "height" =>80),
    array("width" =>71, "height" =>52),
    array("width" =>400, "height" =>300),
    array("width" =>60, "height" =>60),
    array("width" =>180, "height" =>135),
    array("width" =>90, "height" =>60),
    array("width" =>220, "height" =>152),
    array("width" =>160, "height" =>120),
    array("width" =>96, "height" =>62),
    array("width" =>82, "height" =>46),
    array("width" =>200, "height" =>200),
    array("width" =>145, "height" =>95),
    array("width" =>240, "height" =>95),
    array("width" =>40, "height" =>40),
    array("width" =>450, "height" =>300),
    array("width" =>320, "height" =>240),
    array("width" =>104, "height" =>78),
    array("width" =>300, "height" =>80),
    array("width" =>600, "height" =>160),
    array("width" =>214, "height" =>160),
    array("width" =>180, "height" =>180),
    array("width" =>320, "height" =>180),
    array("width" =>640, "height" =>480),
    array("width" =>640, "height" =>0),
    array("width" =>640, "height" =>320),
    array("width" =>2048, "height" =>1536),
    array("width" =>1536, "height" =>2048),
    array("width" =>1024, "height" =>768),
    array("width" =>768, "height" =>1024),
    array("width" =>360, "height" =>200),
    array("width" =>600, "height" =>300),
    array("width" =>380, "height" =>300),
    array("width" =>210, "height" =>300),
    array("width" =>240, "height" =>240),
    array("width" =>1536, "height" =>520),
    array("width" =>2048, "height" =>520),
    array("width" =>950, "height" =>600),
    array("width" =>160, "height" =>40),
    array("width" =>240, "height" =>163),
    array("width" =>160, "height" =>90),
    array("width" =>600, "height" =>240),
    array("width" =>300, "height" =>200),
    array("width" =>355, "height" =>200),
    array("width" =>300, "height" =>100),
    array("width" =>172, "height" =>95),
    array("width" =>180, "height" =>300),
    array("width" =>355, "height" =>145),
    array("width" =>410, "height" =>160),
    array("width" =>172, "height" =>150),
    array("width" =>200, "height" =>130),
    array("width" =>172, "height" =>70),
    array("width" =>90, "height" =>90),
    array("width" =>500, "height" =>280),
    array("width" =>560, "height" =>350),
    array("width" =>760, "height" =>305),
    array("width" =>220, "height" =>240),
    array("width" =>220, "height" =>195),
    array("width" =>75, "height" =>75)
);

if ($_REQUEST['order'] == 'height'){
    usort($resolutions, function($a, $b){
        if ($a['height'] > $b['height']){
            return 1;
        } else if ($a['height'] == $b['height'] and $a['width'] > $b['width']){
            return 1;
        } else if ($a['width'] == $b['width'] and $a['height'] == $b['height']){
            return 0;
        } else {
            return -1;
        }
    });
}else{
    usort($resolutions, function($a, $b){
        if ($a['width'] > $b['width']){
            return 1;
        } else if ($a['width'] == $b['width'] and $a['height'] > $b['height']){
            return 1;
        } else if ($a['width'] == $b['width'] and $a['height'] == $b['height']){
            return 0;
        } else {
            return -1;
        }
    });
}

?>
<!DOCTYPE html >
<html>
<head>
    <title>上传文件</title>
</head>
<body>
<div style="font-size: 14px; line-height: 30px">
    <form target="_blank" action="" enctype="multipart/form-data" method="post" >
        <div>
            子系统:
            <select name="subSystem">
                <option value="vnd">vnd</option>
            </select>
        </div>
        <div>
            文件夹：
            <select name="folder">
                <option value="cdn">cdn</option>
                <option value="static">static</option>
            </select>
        </div>
        <div>
            分辨率：
            <select name="resolution">
                <option value="w768_h1024">Default: 768 * 1024</option>
                <?PHP
                foreach ($resolutions as $r) {
                    $s = 'w'.$r['width'].'_h'.$r['height'];
                    echo '<option value="'.$s.'">'.$r['width'].' * '.$r['height'].'</option>';
                }
                ?>
            </select>
            <a href="?order=height">按高度排序</a>
            <a href="?order=width">按宽度排序</a>
        </div>
        <div>
            <input type="checkbox" id="showRawResult" name="showRawResult" />
            <label for="showRawResult">显示原始结果</label>
        </div>
        <input type="file" name="upload_file" >
        <input type="submit" />
    </form>
</div>
</body>
</html>