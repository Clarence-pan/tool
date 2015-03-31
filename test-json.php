<?php

function unicode_convert($t) {
    preg_match_all('/\\\\u[0-9a-f]{4}/', $t, $t_all);

    foreach ($t_all[0] as $ch) {
        $_ch = intval(str_replace('\u', '0x', $ch), 16);
        $uni_ch = mb_convert_encoding('&#' . $_ch . ';', 'UTF-8', 'HTML-ENTITIES');
        $t = str_replace($ch, $uni_ch, $t);
    }

    return $t;
}

function unicode_convert2($t){
    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', function($matches){
        return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");
    }, $t);
}

$s = json_encode(array('test'=>'你好'));

$s = unicode_convert2($s);
echo $s;
