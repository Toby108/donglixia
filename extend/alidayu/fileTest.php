<?php
    include "TopSdk.php";
    date_default_timezone_set('Asia/Shanghai'); 
    header('Content-Type:text/html;charset=utf-8');

    $c = new TopClient;
    $c->appkey = '24349914';
    $c->secretKey = 'bcc57462e62a810e1a946f3c46b244da';
    $req = new AlibabaAliqinFcSmsNumSendRequest;
    $req->setExtend("123456");//选填，公共回传参数
    $req->setSmsType("normal");//短信类型，传入值请填写normal
    $req->setSmsFreeSignName("汇盟商城");//短信签名【汇盟】【汇盟商城】【汇盟E家】
    $req->setSmsParam("{\"code\":\"528406\",\"product\":\"\"}");//短信模板变量
    $req->setRecNum("18576712233");//短信接收号码
    $req->setSmsTemplateCode("SMS_70910313");//短信模板ID
    //模板ID：SMS_70910313；内容：验证码${code}，您正在进行${product}身份验证，打死不要告诉别人哦！
    //模板ID：SMS_71300575；内容：尊敬的${name}，您所出单的保单（${ins_no}），赠送的${type}积分：${score}，已到账。
    $resp = $c->execute($req);
var_dump($resp);
?>