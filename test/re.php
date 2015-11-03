<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');

function test($text){
    $result = preg_replace_callback('~(((t\.cn)|(j\.mp)|(x\.co)|(https?:\/\/([a-zA-Z0-9_-]+\.?)+(:\d+)?))(\/[a-zA-Z0-9_=#\-\/\.\?]*)?)~', function($matches){
        $url = $matches[0];
        $fullUrl = preg_match('~^https?:\/\/~', $url) ? $url : 'http://' . $url;
        // 如果链接是一个图片，则将图片显示出来（比如二维码）
        if (preg_match('~\.(jpg|jpeg|gif|png)$~', $url)){
            return "<a href=\"{$fullUrl}\"><img src=\"{$fullUrl}\" alt=\"{$url}\" /></a>";
        }
        return "<a href=\"{$fullUrl}\">{$url}</a>";
    }, $text);

    echo "<h2>before:</h2>";
    echo "<div>".htmlspecialchars($text)."</div>";
    echo "<h2>after:</h2>";
    echo "<div>".htmlspecialchars($result)."</div>";
}




test('尊敬的客人，您好！您预订的2015-08-10出游的8张<普宁寺景区门票>电子票http://m-test.tuniu.com/，直接扫二维码入园已支付成功，取票手机号为：13940327436，入园凭证为：http://t.cn/RLEtER3，请于出游当天至相应景区凭二维码直接验证入园。点击 t.cn/8FpyI4G 下载App，手机付款安全快捷。');
test('尊敬的客人，您好！您预订的2015-08-11出游的5张<【机动】东部华侨城大侠谷门票>1.5米以上人士预订专用已支付成功，取票手机号为：13682456453，入园凭证为：79022140758903，入园二维码链接: http://smartoct.com:81/ugc/voucher/20150810/1439190016428.jpg，请于出游当天至相应景区凭二维码+身份证换票入园。点击 t.cn/8FpyI4G 下载App，手机付款安全快捷。');

