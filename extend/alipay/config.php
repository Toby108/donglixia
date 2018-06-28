<?php

$config = array(
    //应用ID,您的APPID。
    'app_id' => "2017051707265471",

    //商户私钥，您的原始格式RSA私钥
    'merchant_private_key' => "MIIEowIBAAKCAQEAmnY+oajrL1fxqyBeYfvKs0R4lzHi525oWHYD8MBt4hcyIJ8hlUA29EH4M+4F4TkgyNN0arqMoq0RAoU413TLa/ShIRjwkzEletwbRhMtqrAtENQYeEDru/J920VfUtno8FCSfw1a/jNWvs+vhBh3sNpenU+PbHuTCS4mvBPdY3QKhVfVBlS0Lm2Td/z1tF15WWmWkBDxgmKjC/UQDiHObG4k/aRt92+MtHZD5CtCe+d9zQrGACV9hQld7ZVbh/l/heU70zd0GayCTB7fzeVM8Edrf4MiFsT8RCOinHynzhL0druu5uwf5eK98ZTQmOZbpaen32Q9hKoZ+9k9NpraGQIDAQABAoIBAGbBLt1AkGSIbr7W47LvXGNxs39xsm8fZc8jg2+PtAnqQHCvLBosA+LuV/3D5Kl15bdz5Eh0HcbCt0Qj+DJtAj6pGN/5xWFGGpbnAkXV0FVyqig90KF/HtODd/J87fMh//KOx0Y6TkQ5RwXdB/gqhsViH/sqyFD0zFYZG6+Wz3qu2RU3sa/UYqft/O0Z++rr76rNRL+SxIdB1nod5lwpfTcq7C3bRA7yq4wQ/atRargg/si0O4F6bHmiErS9W5l2WYoCPOUH4Q/jipThTBUpodO9FmG2oNFk9H8W2i1EKd7i+mgzwhVfYaeQS4KKsYpF6Vm6oQLwWOV1LKJPoIHZ42ECgYEAx8Ylhe/3b0XHBVfoTclgptAxuHQE/mgvSUQ/3FRJD/ltcwscnAIRIWxsV+Q7E+G9Jn8teZQBq/J1B6fWr2uqZLbnD0Svdm02MeyeQTE72RfOgbc2L9xgb+XgIFHZ9fehlKGJq64EDZfDdKzx+KS2t7D9Up3o+YxikasDsujct70CgYEAxe9URyzIVVAVwDelFc9MAaWw7IOa55Hmy5yVOKVs4dE4pU5/lp3Ty0UNflXuPn55vAsDedY7h4RIwLlTVVH0TwHMGAJ4yEbROAamjsRecKGryTlr2jZRrUkR+n+6AsrSysl/adHq9FRtyUfjTdS9Y3wy9FOAAssu8NNaBAGbM40CgYBMbRGN2+/dMeShtq+4koHXT262EiyW52Svstx7fgM1iUbF7EpVpLTJBKGuPjIbuRCT2bOb/3NkRK1g8GTBV38oaJCc1roJpF5HWy5v4lXzB5iJJ34jcTuzk03bD3VDFClMoz/33erPCHBOApvPc7QzMhK3uJGYZoyPDc2sdEl8QQKBgHDndrRuyAtZ9j5Bv2o0Z8+cutH+s/KZmAPW2ouIWRZqaJxqrX61omUVi3/f9lkxfbEUuzPZTvWbMIC2deF6MRiMFuYvKsRbGOaTbJiNTK2Emt/aYDoFuAtXXej8yJblKN5Bl+s9sX8TVdh1qCPyUOGZq9sQcjmluq3mcTdNz0DVAoGBALhDs3yWezKbJna4wjmlpcOZTOWYQIi+mv3cyjkpPzWE2YQPRiTlFUk/T2HFMjC6CYQrvKNoFuCc52JhKms8gMjlU9vfMS+WnbPyHPIi0X+DkzkLRcvl6kBQ0bLhp6IrPf9vhJsq+IVKpHEkT3MlyGtRuNz7u5HLR0R1aZRzXf4Q",

    //异步通知地址
    'notify_url' => $_SERVER['HTTP_X_FORWARDED_PROTO']."://".$_SERVER['HTTP_HOST']."/shop/mobile_index/getData?controller=order&action=alipayAsyncCallback",

    //同步跳转
    'return_url' => $_SERVER['HTTP_X_FORWARDED_PROTO']."://".$_SERVER['HTTP_HOST']."/shop/mobile_index/getData?controller=order&action=alipaySyncCallback",

    //编码格式
    'charset' => "UTF-8",

    //签名方式
    'sign_type' => "RSA2",

    //支付宝网关
    'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

    //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
    'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArtxYFvsG+dCjTPjS9GPbky4IGolPG5InxPZgy0CMsdKsm1z1NaPYoRe7xqYmUhmfY0ZmQwLLXnX0Y2xHcvmV4HJVOVsGnPhDi9vBVUj6gfxOQlH+WASGVVzZF6ntm/sgYXbP7S69EK89nict56ePaj8uKvFhLOU1bf9Bp7Qm+Eg38aMLv66GnANElctht3h2cbgiluWA5BBUTqaBCko2E1RKoZ32tVl24wDkArb2eBGY2bQ1xFi+Gu340kBU7U7BvlolEIUXRTMDGt3G2ToE3OLZx+g2VM+G6eQQb7xl58ipuxwGM4gd0/a/gAF+d8AF7hvqXCU3b8VhRhCzBUikPQIDAQAB"
);

