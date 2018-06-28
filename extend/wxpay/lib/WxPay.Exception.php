<?php
/**
 * 
 * 微信支付API异常类
 * @author widyhu
 *
 */

class WxPayException extends \think\Exception {
	public function errorMessage()
	{
		return $this->getMessage();
	}
}
