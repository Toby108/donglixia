<?php
class Core {
    const tokenUrl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s'; // 获取TOKEN
    const getticketUrl = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=%s&type=jsapi'; // 获取票据
    const authorizeUrl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=%s&state=STATE#wechat_redirect'; // 用户同意授权，获取code
    const accessTokenUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code'; // 获取OPENID
    const getMediaUrl = 'http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=%s&media_id=%s'; //获取媒体资源
    const getUserInfo = 'https://api.weixin.qq.com/sns/userinfo?access_token=%s&openid=%s&lang=zh_CN'; //获取用户信息 和 UnionID

    /**
     * 获取分享配置
     * @param string $url
     * @return array
     */
    public static function GetShareConfig($url = '', $appid, $appsecret) {
        $noncestr = self::GetRandCode();
        $jsapi_ticket = self::GetJsApiTicket($appid, $appsecret);
        $timestamp = time ();
        if(empty($url)){
            $url = $_SERVER ['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER ['SERVER_NAME'] . $_SERVER ['REQUEST_URI'];
        }
        $signature = 'jsapi_ticket=' . $jsapi_ticket . '&noncestr=' . $noncestr . '&timestamp=' . $timestamp . '&url=' . $url;
        $signature = sha1 ( $signature );
        $_info = array ();
        $_info ['appId'] = $appid;
        $_info ['jsapi_ticket'] = $jsapi_ticket;
        $_info ['timestamp'] = $timestamp;
        $_info ['noncestr'] = $noncestr;
        $_info ['signature'] = $signature;

        return $_info;
    }

    /**
     * 获取访问Token
     * @return mixed
     */
    public static function GetAccessToken($appid, $appsecret) {
        $Token = array ();

        $flag = self::ReadFlag('WxToken');
        if (!empty($flag)) {
            $Token = $flag ['BaoLian'] ['WxToken'] ['data'];
        }else{
            $url = sprintf (self::tokenUrl, $appid, $appsecret );
            $res = (new self())->HttpCurl ( $url );
            $Token = json_decode ( $res, true );
            $data = [];
            $data['BaoLian']['WxToken'] = ['create' => time(), 'data' => $Token];
            self::writeFlag('WxToken', $data);
        }
        return $Token;
    }

    /**
     * 保存标识
     * @param string $key
     * @param [] $data
     */
    public static function writeFlag($key, $data){
        $data = json_encode($data);
        cookie($key, $data, array('expire' => 7000));
    }

    /**
     * 读取标识
     * @param $key
     * @return bool|mixed
     */
    public static function ReadFlag($key){
        $flag = cookie($key);
        $flag = json_decode($flag,true);
        if (time() - intval($flag ['BaoLian'] [$key] ['create']) > 7000) {
            return false;
        }

        return $flag;
    }

    /**
     * 获取票据
     */
    public static function GetJsApiTicket($appid, $appsecret) {
        $JsapiTicket = array ();

        $flag = self::ReadFlag('WxJsapiTicket');
        if (!empty($flag)) {
            $JsapiTicket = $flag ['BaoLian'] ['WxJsapiTicket'] ['data'];
        }else{
            $access_token = self::GetAccessToken($appid, $appsecret);
            $url = sprintf ( self::getticketUrl, $access_token ['access_token'] );
            $res = (new self())->HttpCurl ( $url );
            $JsapiTicket = json_decode ( $res, true );

            $data = [];
            $data['BaoLian']['WxJsapiTicket'] = ['create' => time(), 'data' => $JsapiTicket];
            self::writeFlag('WxJsapiTicket', $data);
        }
        return $JsapiTicket ['ticket'];
    }

    /**
     * 获取16位随机码
     * @param int $num
     * @return string
     */
    public static function GetRandCode($num = 16) {
        $array = array ("A", 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', "a", 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', "0", '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $tmpstr = '';
        $max = count ( $array );
        for($i = 1; $i <= $num; $i ++) {
            $key = rand ( 0, $max - 1 );
            $tmpstr .= $array [$key];
        }
        return $tmpstr;
    }

    /**
     * 使用curl请求链接
     * @param $url
     * @param string $type
     * @param string $data
     * @return mixed
     */
    protected function HttpCurl($url, $type = 'get', $data = '') {
        // 1，初始化curl
        $ch = curl_init ();
        // 2，设置curl的参数
        $header = ["Accept-Charset: utf-8"];
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE ); // 跳过证书检查
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE ); // 从证书中检查SSL加密算法是否存在
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, $header ); // 请求时发送的header
        curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt ( $ch, CURLOPT_AUTOREFERER, 1 );
        // post提交
        if ($type == 'post') {
            curl_setopt ( $ch, CURLOPT_POST, true );
            curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
        }
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        // 3，执行并获取结果
        $temp = curl_exec ( $ch );
        // 4，关闭curl资源，并且释放系统资源
        curl_close ( $ch );
        return $temp;
    }

    /**
     * 请求302
     * @param $url
     * @param null $data
     * @return bool
     */
    protected function curl_post_302($url, $data = null) {
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 30 );
        curl_setopt ( $ch, CURLOPT_TIMEOUT, 30 );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 ); // 获取转向后的内容
        $data = curl_exec ( $ch );
        $Headers = curl_getinfo ( $ch );
        curl_close ( $ch );
        if ($data != $Headers) {
            return $Headers ["url"];
        } else {
            return false;
        }
    }

}

?>