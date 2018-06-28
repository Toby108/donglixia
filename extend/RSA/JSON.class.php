<?php
/**
 * @link      https://www.zhongan.com
 * @copyright Copyright (c) 2013 众安保险
 */

/**
 * JSON json工具类
 */
class JSON
{
    /**
     * json编码，对不同的php版本json_encode函数进行封装
     * @param mixed  $value 需编码的内容
     * @param int $options 掩码
     * @param int $depth   最大深度
     * @return mixed|string
     */
    public static function encode($value, $options = 0, $depth = 512)
    {
        if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            return json_encode($value, $options | JSON_UNESCAPED_UNICODE, $depth);
        } elseif (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return json_encode($value, $options | JSON_UNESCAPED_UNICODE);
        } else {
            $data = version_compare(PHP_VERSION, '5.3.0', '>=') ? json_encode($value, $options) : json_encode($value);
            return preg_replace_callback(
                "/\\\\u([0-9a-f]{2})([0-9a-f]{2})/iu",
                create_function(
                    '$pipe', 
                    'return iconv(
                        strncasecmp(PHP_OS, "WIN", 3) ? "UCS-2BE" : "UCS-2",
                        "UTF-8",
                        chr(hexdec($pipe[1])) . chr(hexdec($pipe[2]))
                    );'
                ),
                $data
            );
        }
    }
}