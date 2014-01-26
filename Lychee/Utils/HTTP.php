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
 * HTTP tool
 * @author Samding
 * @package Lychee\Utils
 */
class HTTP
{

    /**
     * @var string
     */
    private static $encrypt_key = '*(&*&*(HDWO$_$DA';

    /**
     * return client side ip address
     * @param mixed $default
     * @return string
     */
    public static function getClientIP($default=null)
    {
        $ip_address = '';
        if (!empty($_SERVER['HTTP_CDN_SRC_IP']))
        {
            $ip_address = $_SERVER['HTTP_CDN_SRC_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        elseif (!empty($_SERVER['HTTP_CLIENT_IP']))
        {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['REMOTE_ADDR']))
        {
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }
        elseif (!empty($_SERVER['REMOTE_ADDR']))
        {
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }
        return empty($ip_address)?$default:$ip_address;
    }

    /**
     * return http request param
     * @param string $name
     * @param mixed $default
     * @param string $type
     * @return string
     */
    public static function getParam($name, $default = null, $type='')
    {
        $type = strtolower($type);
        if ($type == 'get') {
            $result = isset($_GET[$name])?$_GET[$name]:$default;
        }
        elseif ($type == 'post') {
            $result = isset($_POST[$name])?$_POST[$name]:$default;
        }
        else {
            $result = isset($_REQUEST[$name])?$_REQUEST[$name]:$default;
        }
        return $result;
    }

    /**
     * get current url
     * @param mixed $default
     * @return string
     */
    public static function getURL($default = null)
    {
        if (! empty($_SERVER["REQUEST_URI"])) {
            $scriptName = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : $default;
            $result = $scriptName;
        } else {
            $scriptName = isset($_SERVER["PHP_SELF"]) ? $_SERVER["PHP_SELF"] : $default;
            if (empty($_SERVER["QUERY_STRING"])) {
                $result = $scriptName;
            } else {
                $tmp = isset($_SERVER["QUERY_STRING"]) ? $_SERVER["QUERY_STRING"] : '';
                $result = $scriptName . '?' . $tmp;
            }
        }
        return $result;
    }

    /**
     * get current url path part
     * @param mixed $default
     * @return string
     */
    public static function getURLPath($default = null)
    {
        $url = self::getURL($default);
        if ($url == $default) {
            return $default;
        }
        $data = parse_url($url);
        return isset($data['path']) ? $data['path'] : $default;
    }

    /**
     * return http referer
     * @param mixed $default
     * @return string
     */
    public static function getReferer($default = null)
    {
        $url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $default;
        return $url;
    }

    /**
     * refresh page
     */
    public static function refresh()
    {
        $html = "<script>";
        $html .= "location.reload()";
        $html .= '</script>';
        echo $html;
        exit();
    }

    /**
     * redirect
     * @param string $url
     */
    public static function redirect($url = '')
    {
        $html = "<script>";
        if (!empty($url)) {
            $html .= "location.href = '{$url}'";
        }
        else {
            $html .= "history.back(-1)";
        }
        $html .= '</script>';
        echo $html;
        exit();
    }

    /**
     * current http request method is post or not
     * @return bool
     */
    public static function isPost()
    {
        return strtolower($_SERVER['REQUEST_METHOD']) == 'post';
    }

    /**
     * current http request is ajax or not
     */
    public static function isAjax()
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * get session
     * @param string $name
     * @param mixed $default
     * @return string
     */
    public static function getSession($name, $default = null)
    {
        $value = isset($_SESSION[$name])?$_SESSION[$name]:$default;
        if ($value === $default) {
            return $default;
        }
        if (!isset($_SESSION[$name . '_md5'])) {
            return $default;
        }
        $encrypt_key = self::$encrypt_key;
        $encrypt = substr(md5($encrypt_key . $value . session_id()), 0, 16);
        if ($_SESSION[$name . '_md5'] == $encrypt) {
            return $value;
        }
        return $default;
    }

    /**
     * set session
     * @param string $name
     * @param mixed $value
     */
    public static function setSession($name, $value)
    {
        $encrypt_key = self::$encrypt_key;
        $encrypt = substr(md5($encrypt_key . $value . session_id()), 0, 16);
        $_SESSION[$name] = $value;
        $_SESSION[$name . '_md5'] = $encrypt;
    }

    /**
     * unset session
     * @param string $name
     */
    public static function unsetSession($name)
    {
        unset($_SESSION[$name]);
        unset($_SESSION[$name . '_md5']);
    }

    /**
     * set cookie
     * @param string $name
     * @param string $value
     * @param int $duration
     * @param string $path
     * @param string|null $domain
     */
    public static function setCookie($name, $value, $duration = 0, $path = '/', $domain = null)
    {
        $encrypt_key = self::$encrypt_key;
        $encrypt = substr(md5($encrypt_key . $value), 0, 16);
        $expire = time() + $duration;
        setcookie($name, $value, $expire, $path, $domain);
        setcookie($name . '_md5', $encrypt, $expire, $path, $domain);
    }

    /**
     * get cookie
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function getCookie($name, $default = null)
    {
        $value = isset($_COOKIE[$name])?$_COOKIE[$name]:$default;
        if ($value === $default) {
            return $default;
        }
        if (!isset($_COOKIE[$name . '_md5'])) {
            return $default;
        }
        $encrypt_key = self::$encrypt_key;
        $encrypt = substr(md5($encrypt_key . $value), 0, 16);
        if ($_COOKIE[$name . '_md5'] == $encrypt) {
            return $value;
        }
        return $default;
    }

    public static function unsetCookie($name)
    {
        setcookie($name, null, time() - 100);
        setcookie($name . '_md5', null, time() - 100);
    }
}