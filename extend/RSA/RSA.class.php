<?php
/**
 * @link      https://www.zhongan.com
 * @copyright Copyright (c) 2013 众安保险
 */
require_once 'JSON.class.php';
/**
 * RSA 工具类，包含加密、解密、签名、验签等逻辑
 */
class RSA
{
    /**
     * @var string 己方私钥
     */
    private $_ownPrivateKey;
    /**
     * @var string 合作方(众安)公钥
     */
    private $_partnerPublicKey;

    /**
     * @param string $ownPrivateKey    己方私钥
     * @param string $partnerPublicKey 合作方(众安)公钥
     */
    public function __construct($ownPrivateKey, $partnerPublicKey)
    {
        $this->_ownPrivateKey    = $ownPrivateKey;
        $this->_partnerPublicKey = $partnerPublicKey;
    }

    /**
     * 解密数据
     * @param string $encryptedData 待解密的数据
     * @return string
     */
    public function decrypt($encryptedData)
    {
        if (empty($encryptedData)) {
            return '';
        }
        $encryptedData = base64_decode($encryptedData);
        $decryptedList = array();
        $step          = 128;
        for ($i = 0, $len = strlen($encryptedData); $i < $len; $i += $step) {
            $data      = substr($encryptedData, $i, $step);
            $decrypted = '';
            openssl_private_decrypt($data, $decrypted, $this->_ownPrivateKey);
            $decryptedList[] = $decrypted;
        }
        return join('', $decryptedList);
    }

    /**
     * 加密数据
     * @param array|string $data 待加密的数据
     * @return string
     */
    public function encrypt($data)
    {
        is_array($data) && ksort($data) && $data = JSON::encode($data);
        $encryptedList = array();
        $step          = 117;
        for ($i = 0, $len = strlen($data); $i < $len; $i += $step) {
            $tmpData   = substr($data, $i, $step);
            $encrypted = '';
            openssl_public_encrypt($tmpData, $encrypted, $this->_partnerPublicKey);
            $encryptedList[] = ($encrypted);
        }
        $encryptedData = base64_encode(join('', $encryptedList));
        return $encryptedData;
    }

    /**
     * 数据签名
     * @param array|string $data 需签名的数据
     * @return string
     */
    public function sign($data)
    {
        is_array($data) && ksort($data) && $data = JSON::encode($data);
        $data = stripslashes($data);
        $res  = openssl_get_privatekey($this->_ownPrivateKey);
        openssl_sign($data, $sign, $res);
        openssl_free_key($res);
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * 数据验签
     * @param array|string $data 待验签的数据
     * @param string       $sign 签名串
     * @return bool
     */
    public function verify($data, $sign)
    {
        is_array($data) && ksort($data) && $data = JSON::encode($data);
        $data   = stripslashes($data);
        $res    = openssl_get_publickey($this->_partnerPublicKey);
        $result = (bool) openssl_verify($data, base64_decode($sign), $res);
        openssl_free_key($res);
        return $result;
    }


}