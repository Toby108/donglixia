<?php
/**
 * 微信服务号
 */
require ('Common/Core.php');
class Wechat extends Core {
    const appid = 'wx245d693558f2fa38';//服务号：汇盟 senfubank@126.com
    const appsecret = 'f8c1276f3e39b5b2c196ff7f72185025';
	const sendTemplateMsg = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=%s'; //发送模板消息

	public function __construct()
	{

	}

	/**
	 * GET
	 * @param $property_name
	 * @return null
	 */
	public function __get($property_name) {
		if (isset ( $this->$property_name )) {
			return ($this->$property_name);
		} else {
			return NULL;
		}
	}

	/**
	 * SET
	 * @param $property_name
	 * @param $value
	 */
    public function __set($property_name, $value) {
		$this->$property_name = $value;
	}
	
	/**
	 * 用户验证
	 * @param string $redirect_uri 重定向地址
	 */
	public static function validate($redirect_uri, $scope = '') {
	    $scope = empty($scope) ? 'snsapi_base' : $scope;//snsapi_userinfo 弹窗提示，确认登录；snsapi_base 不提示，静默授权
		$url = sprintf ( self::authorizeUrl, self::appid, urlencode($redirect_uri), $scope );
		header ( "Location: " . $url );
	}

    /**
     * 获取分享配置
     * @param string $url
     * @return array
     */
    public static function share($url = '') {
        return self::GetShareConfig($url ,self::appid , self::appsecret);
    }

	/**
	 * 获取OPENID和ACCESSTOKEN
	 * @param $code
	 * @return array|mixed
	 */
	public static function GetOpenIdAndAccessToken($code) {
		$OpenAndAccessToken = array();
		$flag = self::ReadFlag('WxOpenid');
		if (!empty($flag)) {
            $OpenAndAccessToken = $flag ['BaoLian'] ['WxOpenid'] ['data'];
		}else{
            $url = sprintf ( self::accessTokenUrl, self::appid, self::appsecret, $code );
			$res = (new self())->HttpCurl ( $url );
			$OpenAndAccessToken = json_decode($res, true);
			$data = [];
			$data['BaoLian']['WxOpenid'] = ['create' => time(), 'data' => $OpenAndAccessToken];
			self::writeFlag('WxOpenid', $data);
		}
		return $OpenAndAccessToken;
	}

	/**
	 * 获取用户信息
	 * @param $code
	 * @return mixed
	 */
	public static function getUserInfo($code) {
		$flag = self::ReadFlag('WxUserInfo');
		if (!empty($flag)) {
			$UserInfo = $flag ['BaoLian'] ['WxUserInfo'] ['data'];
		}else{
            //获取OpenId 和 AccessToken
            $openid_token = self::GetOpenIdAndAccessToken($code);
			$url = sprintf(self::getUserInfo, $openid_token['access_token'], $openid_token['openid']);
			$res = (new self())->HttpCurl($url);
			$UserInfo = json_decode($res, true);

			$data = [];
			$data['BaoLian']['WxUserInfo'] = ['create' => time(), 'data' => $UserInfo];
			self::writeFlag('WxUserInfo', $data);
		}
		return $UserInfo;
	}

    /**
     * 获取服务器上的图片
     * @param $media_id
     * @return bool|string
     */
    public static function GetServerMedia($media_id){
        $access_token = self::GetAccessToken(self::appid, self::appsecret);

        $url = sprintf ( self::getMediaUrl, $access_token ['access_token'], $media_id );
        $filebody = file_get_contents($url);

        return $filebody;
    }

	/**
	 * 发送模板消息
	 * @param $data
	 * @return mixed
	 */
	public function sendTemplateMsg($data)
	{
		$Token = self::GetAccessToken(self::appid, self::appsecret);
		$url = sprintf(self::sendTemplateMsg, $Token['access_token']);
		$res = self::HttpCurl($url,'post',json_encode($data));
		return $res;
	}
}

?>