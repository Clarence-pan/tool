<?php

/**
 * 密码员，对mcrypt进行封装，使加密和解密变得更加简单
 * Class Cryptographer
 * 经过测试，可以使用的加密算法有：
 *     MCRYPT_3DES,
 *     MCRYPT_BLOWFISH,
 *     MCRYPT_BLOWFISH_COMPAT,
 *     MCRYPT_CAST_128,
 *     MCRYPT_CAST_256,
 *     MCRYPT_DES,
 *     MCRYPT_GOST,
 *     MCRYPT_LOKI97,
 *     MCRYPT_RC2,
 *     MCRYPT_RIJNDAEL_128,
 *     MCRYPT_RIJNDAEL_192,
 *     MCRYPT_RIJNDAEL_256,
 *     MCRYPT_SAFERPLUS,
 *     MCRYPT_SERPENT,
 *     MCRYPT_TRIPLEDES,
 *     MCRYPT_TWOFISH,
 *     MCRYPT_XTEA,
 *  可以使用的加密模式有：
 *     MCRYPT_MODE_CBC,
 *     MCRYPT_MODE_ECB,
 *     MCRYPT_MODE_CFB,
 *     MCRYPT_MODE_OFB,
 *     MCRYPT_MODE_NOFB,
 */
class Cryptographer
{
    /**
     * @param string $key 密码
     * @param string $algorithm 加密算法，推荐AES
     * @param string $mode 加密模式，推荐CBC，不支持stream
     */
    public function __construct($key, $algorithm = MCRYPT_RIJNDAEL_128, $mode = MCRYPT_MODE_CBC)
    {
        $this->key = $key;
        $this->algorithm = $algorithm;
        $this->mode = $mode;
    }

    /**
     * 加密
     * @param $text string 明文
     * @return string/bool BASE64编码后的密文，或false（失败时）
     */
    public function encrypt($text)
    {
        $ivSize = mcrypt_get_iv_size($this->algorithm, $this->mode);

        // 使用MCRYPT_RAND产生初始向量一般就足够安全了 —— 如果使用MCRYPT_DEV_RANDOM可能会更安全点，但是速度会比MCRYPT_RAND慢很多
        $iv = mcrypt_create_iv($ivSize, MCRYPT_RAND);

        // 加密数据
        $encryptedData = mcrypt_encrypt($this->algorithm, $this->key, $text, $this->mode, $iv);
        if ($encryptedData === false){
            return false;
        }

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
