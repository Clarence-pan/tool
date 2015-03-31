<?php
array_walk(explode(' ', 'openssl_encrypt mcrypt_encrypt openssl_public_encrypt'),
    function($func){
        echo $func . (function_exists($func) ? ' ':' NOT ').'support!'.PHP_EOL;
    });

/**
 * 加密者，对mcrypt进行封装，使加密和解密变得更加简单
 * Class Cryptographer
 */
class Cryptographer
{
    /**
     * @param string $key 密码
     * @param string $algorithm 加密算法，推荐AES
     * @param string $mode 加密模式，推荐CBC，不支持stream
     */
    public function __construct($key, $algorithm = self::DEFAULT_ALGORITHM, $mode = self::DEFAULT_MODE)
    {
        $this->key = $key;
        $this->algorithm = $algorithm;
        $this->mode = $mode;
    }

    /**
     * 加密
     * @param $text string 明文
     * @return string BASE64编码后的密文
     */
    public function encrypt($text)
    {
        $ivSize = mcrypt_get_iv_size($this->algorithm, $this->mode);
        echo 'IV size: '.$ivSize.PHP_EOL;
        // 创建初始向量的时候，如果使用MCRYPT_DEV_RANDOM可能会安全点，但是速度会比MCRYPT_RAND慢很多
        $iv = mcrypt_create_iv($ivSize, MCRYPT_RAND);
        $encryptedData = mcrypt_encrypt($this->algorithm, $this->key, $text, $this->mode, $iv);

        // 加密后的数据是二进制的数据 —— 为了便于存储，使用BASE64进行编码，转换为可见字符
        return base64_encode($iv.$encryptedData);
    }

    /**
     * 解密
     * @param $encryptedData string 密文（BASE64编码后的）
     * @return string 明文
     * @throws Exception
     */
    public function decrypt($encryptedData)
    {
        $encryptedData = base64_decode($encryptedData);
        $ivSize = mcrypt_get_iv_size($this->algorithm, $this->mode);
        if (strlen($encryptedData) < $ivSize) {
            throw new InvalidArgumentException('The encrypted data is too short: '.strlen($encryptedData));
        }

        $iv = substr($encryptedData, 0, $ivSize);
        $encryptedData = substr($encryptedData, $ivSize);
        $text = mcrypt_decrypt($this->algorithm, $this->key, $encryptedData, $this->mode, $iv);
        return rtrim($text, "\0");
    }

    /**
     * string 默认加密算法，RIJNDAEL即AES，128位密码足够安全了
     */
    const DEFAULT_ALGORITHM = MCRYPT_RIJNDAEL_128;

    /**
     * string 默认加密模式，推荐使用CBC，安全性高
     */
    const DEFAULT_MODE   = MCRYPT_MODE_CBC;

    /**
     * @var string 密码
     */
    private $key;

    /**
     * @var string 加密算法
     */
    private $algorithm;

    /**
     * @var string 加密模式
     */
    private $mode;
}

// key => data
$tests = array(
    'test key' => 'test data',
    'sa3DF#$2301a' => '129390128903812903'
);

$algorithms = array(
//    MCRYPT_3DES,
//    MCRYPT_BLOWFISH,
//    MCRYPT_BLOWFISH_COMPAT,
//    MCRYPT_CAST_128,
//    MCRYPT_CAST_256,
//    MCRYPT_DES,
//    MCRYPT_GOST,
//    MCRYPT_LOKI97,
//    MCRYPT_RC2,
//    MCRYPT_RIJNDAEL_128,
//    MCRYPT_RIJNDAEL_192,
//    MCRYPT_RIJNDAEL_256,
//    MCRYPT_SAFERPLUS,
//    MCRYPT_SERPENT,
//    MCRYPT_TRIPLEDES,
//    MCRYPT_TWOFISH,
//    MCRYPT_XTEA,

    // 尚有问题的加密算法：
    MCRYPT_RC6,
//    MCRYPT_PANAMA,
//    MCRYPT_SKIPJACK,
//    MCRYPT_ARCFOUR_IV,
//    MCRYPT_ARCFOUR,
//    MCRYPT_CRYPT,
//    MCRYPT_SAFER64,
//    MCRYPT_SAFER128,
//    MCRYPT_THREEWAY,
//    MCRYPT_WAKE,
//    MCRYPT_IDEA,
//    MCRYPT_MARS,
//    MCRYPT_ENIGNA,
);

$modes = array(
    MCRYPT_MODE_CBC,
    MCRYPT_MODE_ECB,
    MCRYPT_MODE_CFB,
    MCRYPT_MODE_OFB,
    MCRYPT_MODE_NOFB,
//    MCRYPT_MODE_STREAM // stream模式加密好像有问题，无法成功
);

/**
 * @param $key
 * @param $data
 * @param $algorithm
 * @param $mode
 */
function test_encrypt($key, $data, $algorithm, $mode) {
    echo "Test [$key => $data] (algorithm: $algorithm mode: $mode )" . PHP_EOL;
    flush();
    $cryptographer = new Cryptographer($key, $algorithm, $mode);
    $encrypted = $cryptographer->encrypt($data);
    echo 'Encrypted: ' . $encrypted . PHP_EOL;
    $decrypted = $cryptographer->decrypt($encrypted);
    echo 'Decrypted: ' . $decrypted . PHP_EOL;
    echo 'Equal: ' . ($decrypted === $data ? 'YES' : 'NO') . PHP_EOL;
    if ($decrypted !== $data) {
        echo "Error:  $encrypted  !=  $data (algorithm: $algorithm mode: $mode )" . PHP_EOL;
    }
    flush();

    return $decrypted === $data;
}


$result = array();

foreach ($algorithms as $algorithm) {
    foreach ($modes as $mode) {
        foreach ($tests as $key => $data) {
            $pass = test_encrypt($key, $data, $algorithm, $mode);
            $result[] = array('algorithm' => $algorithm, 'mode' => $mode, 'pass' => $pass);
        }
    }
}

require(__DIR__.'/../protected/components/DataQuery.php');

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
        'pass' => DataQuery::from($group)->where(array('pass' => true))->count(),
        'fail' => DataQuery::from($group)->where(array('pass' => false))->count()
    );
})->toArray());


