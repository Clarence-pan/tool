<?php

require(__DIR__.'/Cryptographer.php');

// 初始化，指定密码、加密算法
$cryptographer = new Cryptographer("test-password", MCRYPT_RIJNDAEL_128);

// 加密
$encrypted = $cryptographer->encrypt('test-data');
echo $encrypted.PHP_EOL;    // jS1aqgmHsJrH2n4+Iy35WuOTffeBdGdjASeuflAsPdM=

// 解密
$decrypted = $cryptographer->decrypt($encrypted);
echo $decrypted.PHP_EOL;   // test-data