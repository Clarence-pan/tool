<?php

if (!extension_loaded('mcrypt')){
    die('Need mcrypt extension.');
}

require(__DIR__.'/Cryptographer.php');
require(__DIR__.'/../protected/components/DataQuery.php');

srand(time());
function generate_random_string($len){
    $s = '';
    for ($i = 0; $i < $len; $i++){
        $s .= chr(rand(33,126));
    }
    return $s;
}

function kassert($condition){
    if (!$condition){
        throw new Exception("Assert failed!");
    }
}


$algorithms = array(
    MCRYPT_3DES,
    MCRYPT_BLOWFISH,
    MCRYPT_BLOWFISH_COMPAT,
    MCRYPT_CAST_128,
    MCRYPT_CAST_256,
    MCRYPT_DES,
    MCRYPT_GOST,
    MCRYPT_LOKI97,
    MCRYPT_RC2,
    MCRYPT_RIJNDAEL_128,
    MCRYPT_RIJNDAEL_192,
    MCRYPT_RIJNDAEL_256,
    MCRYPT_SAFERPLUS,
    MCRYPT_SERPENT,
    MCRYPT_TRIPLEDES,
    MCRYPT_TWOFISH,
    MCRYPT_XTEA,
);

$modes = array(
    MCRYPT_MODE_CBC,
    MCRYPT_MODE_ECB,
    MCRYPT_MODE_CFB,
    MCRYPT_MODE_OFB,
    MCRYPT_MODE_NOFB,
//    MCRYPT_MODE_STREAM // stream模式加密好像有问题，无法成功
);


function test_encrypt($key, $data, $algorithm, $mode) {
    $cryptographer = new Cryptographer($key, $algorithm, $mode);
    $encrypted = $cryptographer->encrypt($data);
    $decrypted = $cryptographer->decrypt($encrypted);
    return array($decrypted === $data, strlen($encrypted), $encrypted);
}

// // 同一种算法，密码长度与密文长度无关
//foreach ($algorithms as $algorithm) {
//    foreach ($modes as $mode) {
//        $data = generate_random_string(10);
//        $len = 0;
//        foreach (range(5, 50) as $i){
//            $key = generate_random_string($i);
//            list($pass , $lenA) = test_encrypt($key, $data, $algorithm, $mode);
////            echo "$lenA, $key".PHP_EOL;
//            if (!$len){
//                $len = $lenA;
//            } else {
//                kassert($len == $lenA);
//            }
//        }
//    }
//}
//die;

//// 检查IV的长度
//// 1. IV的长度与算法有关，与加密模式无关
//// 2. IV长度和算法对应关系：
////     | IV length | algorithms                                                           |
////     |         8 | tripledes, blowfish, blowfish-compat, cast-128, des, gost, rc2, xtea |
////     |        16 | cast-256, loki97, rijndael-128, saferplus, serpent, twofish          |
////     |        24 | rijndael-192                                                         |
////     |        32 | rijndael-256                                                         |
//call_user_func(function() use ($algorithms, $modes){
//    //echo "algorithm, mode, IV_len".PHP_EOL;
//    $result = array();
//    foreach ($algorithms as $algorithm) {
//        foreach ($modes as $mode) {
//            $ivLen = mcrypt_get_iv_size($algorithm, $mode);
//    //        echo "$algorithm, $mode, $ivLen".PHP_EOL;
//            if (isset($result[$algorithm]['ivLen'])){
//                kassert($result[$algorithm]['ivLen'] == $ivLen);
//            }
//
//            $result[$algorithm]['algorithm'] = $algorithm;
//            $result[$algorithm]['ivLen'] = $ivLen;
//        }
//    }
//
//    echo '| IV length | algorithms |'.PHP_EOL;
//    DataQuery::from(array_values($result))->groupBy('ivLen')->map(function($group){
//        echo '| '.$group[0]['ivLen'].' | '.implode(', ', DataQuery::from($group)->column('algorithm')).' |'.PHP_EOL;
//    });
//    die;
//});



// 密文长度和明文长度直接的关系：
// 1. OFB, CFB, NOFB的加密是逐字节加密的：
//       密文长度 = IV长度 + 明文长度
// 2. ECB和CBC是基于块进行加密的：
//       密文长度 = IV长度 + 向下取整((明文长度 + 块长度 - 1) / 块长度) * 块长度
//       $encryptedLen  = $ivLen + floor(($dataLen + $blockLen - 1) / $blockLen) * $blockLen
//    其中：块长度 = IV长度
// 3. 考虑到MYSQL的存储方便性，对生成的密文进行了BASE64编码，编后的长度这样计算：
//       编码后长度 = 向下取整((编码前长度 + 2) / 3) * 4
//       $encodedLen = floor(($dataLen + 2) / 3) * 4
call_user_func(function() use ($algorithms, $modes){
    // 固定长度的密码,
    $tests = array_map(function($i){
            return array(generate_random_string(10), generate_random_string($i));
        }, range(1, 50, 1));

    $calcBase64EncodeLen = function($x){
        return intval(floor(($x + 2) / 3) * 4);
    };

    $calcBlockEncryptLen = function($dataLen, $blockLen){
        return intval(floor(($dataLen + $blockLen - 1) / $blockLen) * $blockLen);
    };

    // NOO
    echo "algorithm, mode, data_len, encrypt_len, IV_len, encrypted".PHP_EOL;
    foreach ($algorithms as $algorithm) {
        foreach ($modes as $mode) {
            foreach ($tests as $oneTest) {
                list($key, $data) = $oneTest;
                list($pass, $encryptedLen, $encrypted) = test_encrypt($key, $data, $algorithm, $mode);
                $result[] = array('algorithm' => $algorithm, 'mode' => $mode, 'pass' => $pass);
                $keyLen = strlen($key);
                $dataLen = strlen($data);
                $ivLen = mcrypt_get_iv_size($algorithm, $mode);
                $encryptedHex = bin2hex($encrypted);
                if (in_array($mode, array('ofb', 'cfb', 'nofb'))){
                    kassert($encryptedLen === $calcBase64EncodeLen($ivLen + $dataLen));
                } else {
                    kassert($encryptedLen === $calcBase64EncodeLen($ivLen + $calcBlockEncryptLen($dataLen, $ivLen)));
                    echo "$algorithm, $mode, $dataLen, $encryptedLen, $ivLen, $encrypted".PHP_EOL;
                }
            }
        }
    }

    die;
});


// 测试哪些算法可用
call_user_func(function() use ($algorithms, $modes){
    $result = array();

    $shouldFailAlgorithms = array(
        MCRYPT_RC6,
        MCRYPT_PANAMA,
        MCRYPT_SKIPJACK,
        MCRYPT_ARCFOUR_IV,
        MCRYPT_ARCFOUR,
        MCRYPT_CRYPT,
        MCRYPT_SAFER64,
        MCRYPT_SAFER128,
        MCRYPT_THREEWAY,
        MCRYPT_WAKE,
        MCRYPT_IDEA,
        MCRYPT_MARS,
        MCRYPT_ENIGNA,
    );

    $algorithms = array_merge($algorithms, $shouldFailAlgorithms);

    // key => data
    $tests = array_merge(
    // 固定长度的密码,
        array_map(function($i){
            return array(generate_random_string(10), generate_random_string($i));
        }, range(5, 50, 1))
    // 固定长度的明文 -- 密码长度与密文长度无关
        ,array_map(function($i){
            return array(generate_random_string($i), generate_random_string(10));
        }, range(1,10,1))
    );

    foreach ($algorithms as $algorithm) {
        foreach ($modes as $mode) {
            foreach ($tests as $oneTest) {
                list($key, $data) = $oneTest;
                list($pass, $encryptedLen) = test_encrypt($key, $data, $algorithm, $mode);
                $result[] = array(
                    'algorithm' => $algorithm,
                    'mode' => $mode,
                    'pass' => $pass,
                );
            }
        }
    }
    print_r(DataQuery::from($result)->groupBy('algorithm')->map(function ($group) {
        return array(
            'total' => count($group),
            'pass' => DataQuery::from($group)->where(array('pass' => true))->count(),
            'fail' => DataQuery::from($group)->where(array('pass' => false))->count()
        );
    })->toArray());
    print_r(DataQuery::from($result)->groupBy('mode')->map(function ($group) {
        return array(
            'total' => count($group),
            'pass' => DataQuery::from($group)->where(array('pass' => true))->count(),
            'fail' => DataQuery::from($group)->where(array('pass' => false))->count()
        );
    })->toArray());
    print_r(DataQuery::from($result)->groupBy('pass')->map(function ($group) {
        return array(
            'total' => count($group),
            'algorithms' => DataQuery::from($group)->groupBy('algorithm')->map(function($a){ return count($a);})->toArray(),
            'mode' => DataQuery::from($group)->groupBy('mode')->map(function($a){ return count($a);})->toArray(),
        );
    })->toArray());

    $failedAlgorithms = DataQuery::from($result)->where(array('pass' => false))->column('algorithm');


    print_r(array(
        'Unexpected failed algorithms'=>array_diff($failedAlgorithms, $shouldFailAlgorithms),
        'Should failed algorithms' => array_diff($shouldFailAlgorithms, $failedAlgorithms)
    ));

});
