<?php

/**
 * Class MultiCurl
 * 对MultiCurl的包装
 * @package admin
 */
class MultiCurl {
    /**
     * @var array SingleUrl的数组
     */
    private $urls = null;  // array of SingleUrl
    /**
     * @var array SingleUrl 正在运行的CURL
     */
    private $runningCurls = array();
    /**
     * @var array SingleUrl 待运行的CURL
     */
    private $awaitingCurls = array();
    /**
     * @var null|resource MultiCURL的句柄
     */
    private $multiCurlHandle = null;
    /**
     * @var int 最大并发数
     */
    private $maxConcurrenceCount = 10;
    /**
     * @param array $urls  like array( array( 'url' => 'http://test.org/...",
     *                                      "param" => array( 'key' => 'value'),
     *                                      'method' => 'POST/GET...',
     *                                      'format' => 'raw/base64'
     *                                       ),
     *                             ... )
     * @see addUrl
     */
    public function __construct(array $urls = null){
        if ($urls != null){
            $this->addUrls($urls);
        }
        $this->multiCurlHandle = curl_multi_init();
    }

    /**
     * @param $url   string: "http://test.org/..."
     *                array like: array( 'url' => 'http://test.org/...",
     *                                      "param" => array( 'key' => 'value'),
     *                                      'method' => 'POST/GET...')
     * @param array $param 传递给URL的参数，格式: array( 'key' => 'value')
     * @param string $method HTTP请求的类型, GET, POST, DELETE, PUT...
     * @param string $format  参数格式化类型，raw表示拼接后的原始参数，base64表示用base64进行加密后再请求，
     *                                         null表示使用setDefaultFormat设置的默认值，初始化为base64json
     * @param array $options CURL的OPTION列表
     * @return self
     */
    public function addUrl($url, array $param=null, $method="GET", $format=null, $options=null){
        $singleCurl = new SingleUrl($url, $param, $method, $format, $options);
        $this->urls[] = $singleCurl;
        if (count($this->runningCurls) < $this->maxConcurrenceCount){
            $this->runningCurls[] = $singleCurl;
            $singleCurl->addToMultiCurl($this);
        } else {
            $this->awaitingCurls[] = $singleCurl;
        }
        return $this;
    }

    /**
     * @param array $urls  like array( array( 'url' => 'http://test.org/...",
     *                                      "param" => array( 'key' => 'value'),
     *                                      'method' => 'POST/GET...',
     *                                      'format' => 'raw/base64'
     *                                       ),
     *                             ... )
     * @see addUrl
     * @return self
     */
    public function addUrls(array $urls){
        foreach ($urls as $url) {
            $this->addUrl($url['url'], $url['param'], isset($url['method']) ? $url['method'] : "GET", $url['format']);
        }
        return $this;
    }

    /**
     * @see curl_multi_setopt
     * @param $option
     * @param $value
     * @return bool
     */
    public function setMultiCurlOpt($option, $value){
        return curl_multi_setopt($this->multiCurlHandle, $option, $value);
    }


    /**
     * @param bool $waitTillEnd 是否需要等到请求结束
     * @return self
     */
    public function exec($waitTillEnd=false, $returnResult=false){
        Yii::log("exec: $waitTillEnd, $returnResult");

        // run
        $this->running = false;

        /**
         * curl_multi_perform(3) is asynchronous. It will only execute as little as possible and then return back control
         * to your program. It is designed to never block. If it returns CURLM_CALL_MULTI_PERFORM you better call it again
         * soon, as that is a signal that it still has local data to send or remote data to receive."
         */
        do {
            $this->mrc = curl_multi_exec($this->multiCurlHandle, $this->running);
        } while($this->mrc == CURLM_CALL_MULTI_PERFORM);

        if ($waitTillEnd){
            $this->wait();
        }

        if ($returnResult){
            return $this->getResults();
        }

        return $this;
    }

    /**
     * wait until end of the request
     * @param $dealResultCallback callable - 当收到数据后处理的函数
     * @return self
     */
    public function wait($dealResultCallback=null){
        Yii::log("wait: running: {$this->running}, mrc: {$this->mrc}" . __FUNCTION__);
        while ($this->running && $this->mrc == CURLM_OK) {
            $this->tryGetResult($dealResultCallback);
            if (curl_multi_select($this->multiCurlHandle) != -1){
                do {
                    $this->mrc = curl_multi_exec($this->multiCurlHandle, $this->running);
                    Yii::log("wait: {$this->mrc} = curl_multi_exec({$this->multiCurlHandle}, {$this->running})  while ({$this->mrc} == ".CURLM_CALL_MULTI_PERFORM."))" . __FUNCTION__);
                }while($this->mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
        $this->tryGetResult($dealResultCallback);
        return $this;
    }

    /**
     * 尝试获取multi curl的结果，如果获取到，则调用callback来处理结果
     * @param $dealResultCallback
     */
    private function tryGetResult($dealResultCallback){
        while ($done = curl_multi_info_read($this->multiCurlHandle)){
            $curlHandle = $done['handle'];
            $singleCurl = $this->findUrlByCurlHandle($curlHandle);
            $singleCurl->fetchContentFromMultiCurl();
            $singleCurl->removeFromMultiCurl();

            self::removeSingleCurlFrom($this->runningCurls, $singleCurl);
            if (!empty($this->awaitingCurls)){
                /** @var SingleUrl $newJob                 */
                $newJob = array_shift($this->awaitingCurls);
                $this->runningCurls[] = $newJob;
                $newJob->addToMultiCurl($this);
            }

            if ($dealResultCallback != null){
                call_user_func_array($dealResultCallback, array($singleCurl));
            }
        }
    }

    /**
     * 根据CURL的handle查找对应的url信息
     * @param $curlHandle
     * @return SingleUrl
     */
    public function &findUrlByCurlHandle($curlHandle){
        foreach ($this->urls as &$singleCurl) {
            if ($singleCurl->getHandle() == $curlHandle){
                return $singleCurl;
            }
        }
    }


    /**
     * 获取所有的结果
     * get results of urls
     * @return array ( array( 'url' => "http://..."
     *                         'resultRaw' => '...raw result...'
     *                         'result' => stdClass(...))
     */
    public function getResults(){
        foreach ($this->urls as &$singleCurl) {
            $singleCurl->fetchContentFromMultiCurl();
        }
        return $this->urls;
    }

    /**
     * cleanup
     */
    public function cleanup(){
        foreach ($this->urls as &$singleCurl){
            $singleCurl->cleanup();
        }
        if ($this->multiCurlHandle != null){
            curl_multi_close($this->multiCurlHandle);
            unset($this->multiCurlHandle);
        }
    }

    /**
     * 获取Multi CURL的句柄
     * @return null|resource
     */
    public function getHandle(){
        return $this->multiCurlHandle;
    }

    /**
     * 设置最大并发数
     * @param $count int 并发数
     */
    public function setMaxConcurrenceCount($count){
        $this->maxConcurrenceCount = $count;
    }

    /**
     * 获取最大并发数
     * @return int 最大并发数
     */
    public function getMaxConcurrenceCount(){
        return $this->maxConcurrenceCount;
    }

    public function __destruct(){
        $this->cleanup();
    }

    /**
     * 从数组中删除一个
     * @param array     $arr
     * @param SingleUrl $singleUrl
     */
    private static function removeSingleCurlFrom(array &$arr, SingleUrl $singleUrl){
        foreach ($arr as $key => $one) {
            if ($singleUrl->getHandle() == $one->getHandle()){
                unset($arr[$key]);
                return;
            }
        }
    }
}

class SingleUrl
{
    /**
     * @see MultiCurl::addUrl
     * @param $url
     * @param $param
     * @param $method
     * @param $format
     */
    public function __construct($url, $param, $method, $format, $options){
        $this->url = $url;
        $this->param = $param;
        $this->method = $method;
        $this->format = $format;
        $this->options = $options;
        $this->init();
    }

    /**
     * 初始化,创建CURL句柄
     * @throws InvalidArgumentException
     */
    private function init(){
        $ch = curl_init();
        $this->curlHandle = $ch;
        $this->setOpt(CURLOPT_URL, $this->url);
        $this->setOpt(CURLOPT_HEADER, true);
        foreach ($this->options as $optionKey => $optionValue) {
            $this->setOpt($optionKey, $optionValue);
        }

        $queryString = null;
        if (!empty($this->param)){
            $queryString = $this->format($this->param, $this->format);
        }
        if ($queryString != null && strtoupper($this->method) != 'POST'){
            $newUrl = $this->url;
            // 如果原始url中本来就有querystring则追加，否则要加?再追加
            if (strstr('?', $this->url)){
                $newUrl .= '&' . $queryString;
            } else {
                $newUrl .= '?' . $queryString;
            }
            $this->setOpt(CURLOPT_URL, $newUrl);
            Yii::log("Made query ULR: " .$newUrl);
        }
        switch (strtoupper($this->method))
        {
            case 'POST':
                $this->setOpt(CURLOPT_POST, true);
                $this->setOpt(CURLOPT_POSTFIELDS, $queryString);
                $this->setOpt(CURLOPT_URL, $this->url);
                break;
            case 'PUT': // 发送文件，必须同时设置inFile和inFileSize
                $this->setOpt(CURLOPT_PUT, true);
                $this->setOpt(CURLOPT_INFILE, $this->inFile);
                $this->setOpt(CURLOPT_INFILESIZE, $this->inFileSize);
                break;
            case 'GET':
                $this->setOpt(CURLOPT_CUSTOMREQUEST, 'GET');
                break;
            case 'HEAD':
                $this->setOpt(CURLOPT_CUSTOMREQUEST, 'HEAD');
                break;
            case 'DELETE':
                $this->setOpt(CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'TRACE';
                $this->setOpt(CURLOPT_CUSTOMREQUEST, 'TRACE');
                break;
            case 'CONNECT':
                $this->setOpt(CURLOPT_CUSTOMREQUEST, 'CONNECT');
                break;
            case 'OPTIONS':
                $this->setOpt(CURLOPT_CUSTOMREQUEST, 'OPTIONS');
                break;
            default:
                throw new InvalidArgumentException("Method " . $this->method .' of URL ' . $this->url .' is not supported!');
        }
        $this->setOpt(CURLOPT_RETURNTRANSFER, true);  // 将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
    }

    public function setOpt($option, $value){
        return curl_setopt($this->curlHandle, $option, $value);
    }


    /**
     * @return string
     */
    public function getDefaultFormat(){
        return $this->defaultFormat;
    }

    /**
     * @param $format
     */
    public function setDefaultFormat($format){
        $this->defaultFormat = $format;
    }

    /**
     * 格式化
     * @param $param
     * @param $format  - null -> use default format, @see getDefaultFormat()
     * @return string
     */
    public function format($param, $format){
        $format = ($format === null ? $this->getDefaultFormat() : $format);
        if (!is_string($param)){
            $param = http_build_query($param);
        }
        switch ($format){
            case 'base64':
                return base64_encode($param);
            case 'base64json':
                $json = json_encode($param);
                return base64_encode($json);
            case 'json':
                return json_encode($param);
            case 'raw':
            default:
                return $param;
        }
    }

    /**
     * @param $param
     * @param $format - null -> use default format, @see getDefaultFormat()
     * @return string
     */
    public function antiFormat($param, $format){
        $format = ($format === null ? $this->getDefaultFormat() : $format);
        switch ($format){
            case 'base64':
                return base64_decode($param);
            case 'base64json':
                $json = base64_decode($param);
                return json_decode($json);
            case 'json':
                return json_decode($param);
            case 'raw':
            default:
                return $param;
        }
    }

    /**
     * 从Multi-CURL中获取结果
     */
    public function fetchContentFromMultiCurl(){
        $this->resultInfo = curl_getinfo($this->curlHandle);
        $this->resultError = curl_error($this->curlHandle);
        $this->resultRaw = curl_multi_getcontent($this->curlHandle);
        $this->resultHeader = substr($this->resultRaw, 0, $this->resultInfo['header_size']);
        $this->resultBodyRaw = substr($this->resultRaw, $this->resultInfo['header_size']);
        $this->resultBody = $this->antiFormat($this->resultBodyRaw, $this->format);
        $this->result = array(
            'info' => $this->resultInfo,
            'error' => $this->resultError,
            'header' => $this->resultHeader,
            'body' => $this->resultBody,
            'bodyRaw' => $this->resultBodyRaw
        );
        return $this;
    }

    public function cleanup(){
        $ch = $this->curlHandle;
        if ($ch){
            curl_close($ch);
        }
        unset($this->curlHandle);

        $this->removeFromMultiCurl();
    }


    /**
     * @param mixed $curlHandle
     */
    private function setCurlHandle($curlHandle)
    {
        $this->curlHandle = $curlHandle;
    }

    /**
     * @return mixed
     */
    public function getHandle()
    {
        return $this->curlHandle;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @return mixed
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $param
     */
    public function setParam($param)
    {
        $this->param = $param;
    }

    /**
     * @return mixed
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * @param array $options
     */
    public function setOptions($options) {
        $this->options = $options;
    }

    /**
     * @return mixed
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        if (empty($this->result)){
            $this->fetchContentFromMultiCurl();
        }
        return $this->result;
    }

    public function getHeaderSize(){
        return $this->resultInfo['header_size'];
    }

    /**
     * @param mixed $resultError
     */
    private function setResultError($resultError)
    {
        $this->resultError = $resultError;
    }

    /**
     * @return mixed
     */
    public function getResultError()
    {
        return $this->resultError;
    }

    /**
     * @param mixed $resultInfo
     */
    private function setResultInfo($resultInfo)
    {
        $this->resultInfo = $resultInfo;
    }

    /**
     * @return mixed
     */
    public function getResultInfo()
    {
        return $this->resultInfo;
    }

    /**
     * @param mixed $resultRaw
     */
    private function setResultRaw($resultRaw)
    {
        $this->resultRaw = $resultRaw;
    }

    /**
     * @return mixed
     */
    public function getResultRaw()
    {
        return $this->resultRaw;
    }

    /**
     * @param mixed $inFile
     */
    public function setInFile($inFile)
    {
        $this->inFile = $inFile;
    }

    /**
     * @return mixed
     */
    public function getInFile()
    {
        return $this->inFile;
    }

    /**
     * @param mixed $inFileSize
     */
    public function setInFileSize($inFileSize)
    {
        $this->inFileSize = $inFileSize;
    }

    /**
     * @return mixed
     */
    public function getInFileSize()
    {
        return $this->inFileSize;
    }

    /**
     * 添加到MultiCurl中去
     * @param MultiCurl $multiCurl
     * @return int
     */
    public function addToMultiCurl(MultiCurl $multiCurl){
        $this->multiCurlAddedTo = $multiCurl;
        Yii::log("addToMultiCurl: Add ".$this->getUrl()." to MultiCurl ".$multiCurl->getHandle());
        return curl_multi_add_handle($multiCurl->getHandle(), $this->getHandle());
    }

    /**
     * 从MultiCurl中去除
     * @return int
     */
    public function removeFromMultiCurl(){
        if (!$this->multiCurlAddedTo){
            return 0;
        }
        $ret = curl_multi_remove_handle($this->multiCurlAddedTo->getHandle(), $this->getHandle());
        unset($this->multiCurlAddedTo);
        return $ret;
    }

    private $url;
    private $curlHandle;
    private $param;
    private $method;
    private $format;
    private $options = array();
    private $resultRaw;
    private $resultHeader;
    private $resultBody;
    private $resultBodyRaw;
    private $result;
    private $resultError;
    private $resultInfo;
    private $defaultFormat;
    private $inFile;
    private $inFileSize;
    /**
     * @var MultiCurl
     */
    private $multiCurlAddedTo;
}

