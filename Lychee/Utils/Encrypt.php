<?php
/**
 * Copyright 2013 koboshi(Samding)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *     http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Lychee\Utils;

/**
 * 加密工具类
 * @author Samding
 * @package Lychee\Utils
 */
class Encrypt
{

    /**
     * 构造器
     * 禁止实例化
     */
    private function __construct()
    {

    }

    /**
     * AES加密
     * @param string $content
     * @return string
     */
    public static function aesEncrypt($content)
    {
        $key = __CLASS__;
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_CBC, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $content);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return base64_encode($iv . $data);
    }

    /**
     * AES解谜
     * @param string $content
     * @return string
     */
    public static function aesDecrypt($content)
    {
        $content = base64_decode($content);
        $key = __CLASS__;
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_CBC, '');
        $iv_size = mcrypt_enc_get_iv_size($td);
        $iv = mb_substr($content, 0, $iv_size);
        mcrypt_generic_init($td, $key, $iv);
        $data = mdecrypt_generic($td, mb_substr($content, $iv_size));
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return trim($data);
    }

    /**
     * 3des加密
     * @param string $content
     * @return string
     */
    public static function desEncrypt($content)
    {
        $key = __CLASS__;
        //3DES CBC mode
        $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $content);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($iv . $data);
        return $data;
    }

    /**
     * 3des解密
     * @param string $content
     * @return string
     */
    public static function desDecrypt($content)
    {
        $key = __CLASS__;
        $content = base64_decode($content);
        //3DES CBC mode
        $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
        $iv_size = mcrypt_enc_get_iv_size($td);
        $iv = mb_substr($content, 0, $iv_size);
        mcrypt_generic_init($td, $key, $iv);
        $data = mdecrypt_generic($td, mb_substr($content, $iv_size));
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $data;
    }
}