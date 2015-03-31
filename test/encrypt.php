<?php

if (!extension_loaded('mcrypt')){
    die('Need mcrypt extension.');
}

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
    public function __construct($key, $algorithm = self::DEFAULT_ALGORITHM, $mode = self::DEFAULT_MODE)
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


/**
 * (表)数据查询类
 * Class DataQuery
 * 使用实例：
 * // 假如有个数据源有productId和productName两列，而希望将productId大于5的数据提出取出来，并且组织成productName => [ xxx ]的形式，则可以：
 * $data = DataQuery::from($dataSource)
 *          ->select(array('productId' => 'id', 'productName'))
 *          ->where(function ($row) {
 *              return $row['id'] > 5;
 *          }, DataQuery::CONDITION_CUSTOM)
 *          ->orderBy('id', SORT_DESC)
 *          ->indexedBy('productName')
 *          ->toArray();
 * 注意：此类已经实现类似Array的下标、遍历和计数等操作，几乎可以直接当作Array来操作。
 */
class DataQuery implements IteratorAggregate,ArrayAccess,Countable {

    /**
     * 不允许直接new，应使用DataQuery::from()来创建实例
     */
    private function __construct(){

    }

    /**
     * 指定数据源
     * @param $dataSource mixed 可以是array或其他格式类似数据库表的数据
     * @return DataQuery
     */
    public static function from($dataSource){
        $instance = new DataQuery();
        $instance->tableData = $dataSource;
        return $instance;
    }

    /**
     * 返回一个空的查询
     * @return DataQuery
     */
    public static function emptyQuery(){
        static $emptyCollection = null;

        if ($emptyCollection === null){
            $emptyCollection = self::from(array());
        }

        return $emptyCollection;
    }

    /**
     * 选择哪些数据 (如果参数为空则表示选择所有数据)
     * @param $keyNames array 指定数据行的key名称，有两种形式：
     * 1. 保持key不变，如 array('id', 'name')
     * 2. 映射key的别名, 如 array('productId' => 'id' , 'productName' => 'name') 表示将原key名productId映射为id并选取
     * 两种形式可以混合使用，比如： array( 'productId' => 'id', 'name')，表示选取原productId为id，并选取name
     * @return DataQuery
     */
    public function select($keyNames){
        if (empty($keyNames)){
            return $this;
        }

        // 统一形式为 key => alias的格式
        $keyAliasMap = array();
        foreach ($keyNames as $key => $alias) {
            // 数字的key，说明不需要映射别名
            if (is_numeric($key)){
                $keyAliasMap[$alias] = $alias;
            }
            // 需要映射别名
            else {
                $keyAliasMap[$key] = $alias;
            }
        }

        unset($keyNames);

        // 转换数据
        $selectedData = array();
        foreach ($this->tableData as $rowData) {
            $selectedItem = array();
            foreach ($keyAliasMap as $key => $alias) {
                $selectedItem[$alias] = $rowData[$key];
            }
            $selectedData[] = $selectedItem;
        }

        $this->tableData = $selectedData;
        return $this;
    }

    /**
     * 映射
     * @param callable $mapFunc 将每一行数据映射为$mapFunc()的返回值, 函数入参即行的数据
     * @return $this
     */
    public function map($mapFunc){
        foreach ($this->tableData as &$rowData) {
            $rowData = call_user_func($mapFunc, $rowData);
        }
        return $this;
    }

    /**
     * 指定过滤条件
     * @param $conditions mixed 参见self::CONDITION_XXX
     * @param $conditionType int 指定条件的类型
     * @return DataQuery
     * @throws InvalidArgumentException
     */
    public function where($conditions, $conditionType = self::CONDITION_SIMPLE_MATCH){
        // 为空表示全部数据
        if (empty($conditions)){
            return $this;
        }

        $filteredData = array();

        // 过滤数据
        switch ($conditionType){
            case self::CONDITION_SIMPLE_MATCH:
                foreach ($this->tableData as $rowData) {
                    // 判断是否匹配所有的条件
                    $isMatch = true;
                    foreach ($conditions as $key => $conditionValue) {
                        if ($rowData[$key] != $conditionValue){
                            $isMatch = false;
                        }
                    }

                    // 匹配则是需要的数据
                    if ($isMatch){
                        $filteredData[] = $rowData;
                    }
                }
                break;

            case self::CONDITION_STRICT_MATCH:
                foreach ($this->tableData as $rowData) {
                    // 判断是否匹配所有的条件
                    $isMatch = true;
                    foreach ($conditions as $key => $conditionValue) {
                        if ($rowData[$key] != $conditionValue){
                            $isMatch = false;
                        }
                    }

                    // 匹配则是需要的数据
                    if ($isMatch){
                        $filteredData[] = $rowData;
                    }
                }
                break;

            case self::CONDITION_CUSTOM:
                // 过滤数据
                foreach ($this->tableData as $rowData) {
                    // 匹配则是需要的数据
                    if (call_user_func($conditions, $rowData)){
                        $filteredData[] = $rowData;
                    }
                }
                break;
            default:
                throw new InvalidArgumentException("Invalid condition type: $conditionType.");
        }

        $this->tableData = $filteredData;
        return $this;
    }

    /**
     * 以$keyName为KEY将数据组织成 $key => $row的形式
     * @param $columnName mixed
     * 1. string 指定某个列为key
     * 2. array 指定多个列为key，key的组合方式是使用空字符$keyNameGlue为间隔符
     * @param $keyNameGlue string 当指定多个列为key时，使用的间隔符
     * @return DataQuery
     */
    public function indexedBy($columnName, $keyNameGlue = "_"){
        $indexedData = array();

        if (!is_array($columnName)){
            // 将数据转换为以$keyName那一列为key的数组
            foreach ($this->tableData as $rowData) {
                $indexedData[$rowData[$columnName]] = $rowData;
            }
        } else {
            // key的组合方式是使用空字符chr(0)为间隔符
            foreach ($this->tableData as $rowData) {
                $keys = array();
                foreach ($columnName as $key) {
                    $keys[] = $rowData[$key];
                }
                $indexedData[implode($keyNameGlue, $keys)] = $rowData;
            }
        }

        $this->tableData = $indexedData;

        return $this;
    }

    /**
     * 根据列来将数据进行分组
     * @param  mixed $columnName 列名
     * 1. string 指定某个列为key
     * 2. array 指定多个列为key，key的组合方式是使用空字符$keyNameGlue为间隔符
     * @param string $groupKeyGlue
     * @return $this
     */
    public function groupBy($columnName, $groupKeyGlue = "_"){
        $groupedData = array();

        if (!is_array($columnName)){
            // 将数据转换为以$keyName那一列为key的数组
            foreach ($this->tableData as $rowData) {
                $groupKey = $rowData[$columnName];
                if (!isset($groupedData[$groupKey])){
                    $groupedData[$groupKey] = array();
                }
                $groupedData[$groupKey][] = $rowData;
            }
        } else {
            // key的组合方式是使用空字符chr(0)为间隔符
            foreach ($this->tableData as $rowData) {
                $keys = array();
                foreach ($columnName as $key) {
                    $keys[] = $rowData[$key];
                }

                // 构造分组的key
                $groupKey = implode($groupKeyGlue, $keys);

                if (!isset($groupedData[$groupKey])){
                    $groupedData[$groupKey] = array();
                }
                $groupedData[$groupKey][] = $rowData;
            }
        }

        $this->tableData = $groupedData;
        return $this;
    }

    /**
     * 排序
     * @param $keyNames mixed
     * 1. string 根据单个key进行排序
     * 2. array 指定多个key进行排序，形如 array('id', 'name') 或
     *      array(
     *           'id' => array( SORT_ASC/SORT_DESC, SORT_REGULAR/SORT_NUMERIC/SORT_STRING),
     *           'name' => array(SORT_ASC),
     *           'age' => SORT_NUMERIC
     *      )
     * @param $orderType int SORT_ASC/SORT_DESC
     * @param $sortFlag int SORT_REGULAR/SORT_NUMERIC/SORT_STRING
     * @return DataQuery
     */
    public function orderBy($keyNames, $orderType = SORT_ASC, $sortFlag = SORT_REGULAR){
        // 先转化为数组
        $this->tableData = $this->toArray();

        // 多个key进行排序
        if (is_array($keyNames)){

            // 构造array_multisort的参数
            $args = array();
            foreach ($keyNames as $key => $option) {
                if (is_numeric($key)){
                    $args[] = DataQuery::from($this->tableData)->column($option);
                    $args[] = $orderType;
                    $args[] = $sortFlag;
                } else {
                    $args[] = DataQuery::from($this->tableData)->column($key);

                    // 只有一个元素，简化处理
                    if (is_array($option) && count($option) == 1){
                        $option = reset($option);
                    }

                    // 增加排序类型和排序标志参数
                    if (is_array($option)){
                        $args[] = $option[0];
                        $args[] = $option[1];
                    } else {
                        if (in_array($option, array(SORT_ASC, SORT_DESC))){
                            $args[] = $option;
                            $args[] = $sortFlag;
                        } else {
                            $args[] = $orderType;
                            $args[] = $sortFlag;
                        }
                    }
                }
            }

            $args[] = &$this->tableData;
            call_user_func_array('array_multisort', $args);
        }
        // 单个key进行排序
        else {
            $sortColumnData = DataQuery::from($this->tableData)->column($keyNames);
            array_multisort($sortColumnData, $orderType, $sortFlag, $this->tableData);
        }
        return $this;
    }

    /**
     * 获取第一列
     * @return array
     */
    public function firstColumn(){
        $data = $this->toArray();
        $data = array_map(function($rowData){
            return reset($rowData);
        }, $data);
        return $data;
    }

    /**
     * 获取某一列
     * @param $columnName string 列名
     * @return array
     */
    public function column($columnName){
        $data = $this->toArray();
        $data = array_map(function($rowData) use ($columnName){
            return $rowData[$columnName];
        }, $data);
        return $data;
    }

    /**
     * 转化为数组
     * @param bool $associated  行数据是否是关联数组
     * @return array
     * @throws InvalidArgumentException
     */
    public function toArray($associated=true){
        if (is_array($this->tableData)){
            if ($associated){
                return $this->tableData;
            } else {
                return array_map(function($rowData){
                    return array_values($rowData);
                }, $this->tableData);
            }
        }

        $arr = array();
        if ($associated){
            foreach ($this->tableData as $rowData) {
                $arr[] = $rowData;
            }
        } else {
            foreach ($this->tableData as $rowData) {
                $arr[] = array_values($rowData);
            }
        }
        return $arr;
    }

    /**
     * 判断是否为空
     * @return bool
     */
    public function isEmpty(){
        if ($this->tableData == null) {
            return true;
        }
        return 0 == $this->count();
    }


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @implements IteratorAggregate::getIterator()
     */
    public function getIterator(){
        return new DataQueryIterator($this->tableData);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @implements ArrayAccess::offsetExists
     */
    public function offsetExists($offset) {
        return isset($this->tableData[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @implements ArrayAccess::offsetGet
     */
    public function offsetGet($offset) {
        return $this->tableData[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @implements ArrayAccess::offsetSet
     */
    public function offsetSet($offset, $value) {
        $this->tableData[$offset] = $value;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @implements ArrayAccess::offsetUnset
     */
    public function offsetUnset($offset) {
        unset($this->tableData[$offset]);
    }

    /**
     * @return int 行数
     * @implements Countable::count()
     */
    public function count() {
        return count($this->tableData);
    }

    // 简单匹配，$conditions形如array('key' => $value, ...), 用于匹配data[key] == $value的数据
    const CONDITION_SIMPLE_MATCH = 0;
    // 严格匹配，$conditions类似简单匹配的格式，但是执行的是===的严格匹配
    const CONDITION_STRICT_MATCH = 1;
    // 自定义匹配，$conditions是可以调用的函数，形如bool function($rowData), 返回true表示要命中的数据
    const CONDITION_CUSTOM = 2;

    private $tableData;
}

/**
 * 针对DataQuery的迭代器
 * Class DataQueryIterator
 */
class DataQueryIterator implements Iterator{

    public function __construct(&$tableData){
        $this->tableData = &$tableData;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current(){
        return current($this->tableData);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next(){
        next($this->tableData);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key(){
        return key($this->tableData);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid(){
        return isset($this->tableData[key($this->tableData)]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind(){
        rewind($this->tableData);
    }


    private $tableData;
}



// key => data
$tests = array(
    'test key' => 'test data',
    'sa3DF#$2301a' => '129390128903812903'
);

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

    // 尚有问题的加密算法：
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

print_r(array(
    'Unexpected failed algorithms'=>array_diff($failedAlgorithms, $shouldFailAlgorithms),
    'Should failed algorithms' => array_diff($shouldFailAlgorithms, $failedAlgorithms)
));
