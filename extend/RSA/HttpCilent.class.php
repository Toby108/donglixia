<?php
/**
 * @link      https://www.zhongan.com
 * @copyright Copyright (c) 2013 众安保险
 */
require_once 'JSON.class.php';
/**
 * HttpClient http工具类
 */
class HttpClient
{
    /**
     * 请求方法 仅有GET和POST
     */
    const GET = 'GET';
    const POST = 'POST';


    /**
     * @var resource curl句柄
     */
    protected $_curl;

    /**
     * 发送GET请求
     * @param string $url       请求url
     * @param array  $params    请求参数数组
     * @param array  $options   需要设置的curl选项数组
     * @return array
     * @throws Exception
     */
    public function get($url, $params = array(), $options = array())
    {
        return $this->_request($url, self::GET, $params, $options);
    }

    /**
     * 发送POST请求
     * @param string $url       请求url
     * @param array  $params    请求参数数组
     * @param array  $options   需要设置的curl选项数组
     * @return array
     * @throws Exception
     */
    public function post($url, $params = array(), $options = array())
    {
        return $this->_request($url, self::POST, $params, $options);
    }

    /**
     * 发送curl请求
     * @param string $url       请求url
     * @param string $method    请求方式 (GET/POST)
     * @param array  $params    请求参数数组
     * @param array  $options   需要设置的curl选项数组
     * @return array
     * @throws Exception
     */
    protected function _request($url, $method = self::GET, $params = array(), $options = array())
    {
        if ($method === self::GET) {
            $url .= (stripos($url, '?') ? '&' : '?').http_build_query($params);
            $params = array();
        }
        $this->_curl = curl_init();
        curl_setopt($this->_curl, CURLOPT_HEADER, 1);
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($this->_curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->_curl, CURLOPT_URL, $url);

        //使用证书情况
        if (isset($options['sslcert_path']) && isset($options['sslkey_path'])) {
            if (!file_exists($options['sslcert_path']) || !file_exists($options['sslkey_path'])) {
                throw new Exception('Certfile is not correct');
            }
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, 2);//严格校验
            curl_setopt($this->_curl, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($this->_curl, CURLOPT_SSLCERT, $options['sslcert_path']);
            curl_setopt($this->_curl, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($this->_curl, CURLOPT_SSLKEY, $options['sslkey_path']);
        } else {
            curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, 0);
        }

        // 上传文件的情况
        if (isset($options['files']) && count($options['files'])) {
            foreach ($options['files'] as $index => $file) {
                $params[$index] = $this->_createCurlFile($file);
            }

            version_compare(PHP_VERSION, '5.5', '<') || curl_setopt($this->_curl, CURLOPT_SAFE_UPLOAD, false);

            curl_setopt($this->_curl, CURLOPT_POST, 1);
            curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $params);
        } else {
            if (isset($options['json'])) { //请求报文为json
                $params = JSON::encode($params);
                $options['headers'][] = 'content-type:application/json';
            }

            curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $params);
        }

        if ($method === self::POST) { //处理POST数据大于1024字节时无法获取服务端返回数据的问题
            $options['headers'][] = 'Expect:';
        }

        // 设置自定义的头信息
        if (isset($options['headers']) && count($options['headers'])) {
            curl_setopt($this->_curl, CURLOPT_HTTPHEADER, $options['headers']);
        }

        // 需要认证的情况
        if (isset($options['auth']['type']) && 'basic' === $options['auth']['type']) {
            curl_setopt($this->_curl, CURLOPT_USERPWD, $options['auth']['username'].':'.$options['auth']['password']);
        }

        $response = curl_exec($this->_curl);

        if (curl_errno($this->_curl)) {
            throw new Exception(curl_error($this->_curl), 1);
        }

        $curlInfo = curl_getinfo($this->_curl);

        curl_close($this->_curl);
        // 分离header和body
        $headerSize = $curlInfo['header_size'];
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        $results = array(
            'curl_info' => $curlInfo,
            'content_type' => $curlInfo['content_type'],
            'status' => $curlInfo['http_code'],
            'headers' => $this->_splitHeaders($header),
            'data' => $body,
        );

        return $results;
    }

    /**
     * 组装curl文件请求
     * @param string $filename 文件路径
     * @return CURLFile|string
     */
    protected function _createCurlFile($filename)
    {
        if (function_exists('curl_file_create')) {
            return curl_file_create($filename);
        }

        return "@$filename;filename=".basename($filename);
    }

    /**
     * 分离头信息(转换为数组)
     * @param string $rawHeaders 原始头信息
     * @return array
     */
    protected function _splitHeaders($rawHeaders)
    {
        $headers = array();

        $lines = explode("\n", trim($rawHeaders));
        $headers['HTTP'] = array_shift($lines);

        foreach ($lines as $h) {
            $h = explode(':', $h, 2);

            if (isset($h[1])) {
                $headers[$h[0]] = trim($h[1]);
            }
        }

        return $headers;
    }
}
