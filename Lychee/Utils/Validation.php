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
 * Validation
 * @author Samding
 * @package Lychee\Utils
 */
class Validation
{

    /**
     * constructor
     */
    private function __construct()
    {

    }

    /**
     * is int or not
     * @param mixed $value
     * @return bool
     */
    public static function isInt($value)
    {
        if (is_string($value) && is_numeric($value)) {
            return true;
        }
        return filter_input(FILTER_VALIDATE_INT, $value);
    }

    /**
     * is string or not
     * @param mixed $value
     * @return bool
     */
    public static function isString($value)
    {
        return is_string($value);
    }

    /**
     * is bool or not
     * @param string|bool $value
     * @return bool
     */
    public static function isBool($value)
    {
        return is_bool($value);
    }

    /**
     * is float or not
     * @param string|float $value
     * @return bool
     */
    public static function isFloat($value)
    {
        return floatval($value) == $value;
    }

    /**
     * is email or not
     * @param string $email
     * @return mixed
     */
    public static function isEmail($email)
    {
        return filter_input(FILTER_VALIDATE_EMAIL, $email);
    }

    /**
     * is chinese zip code or not
     * @param string $code
     * @return bool
     */
    public static function isZipCode($code)
    {
        return preg_match('/^[1-9]\d{5}(?!\d)$/is', $code) > 0;
    }

    /**
     * is mobile or not
     * @param string $mobile
     * @return bool
     */
    public static function isMobile($mobile)
    {
        return preg_match('/^[1][358]\d{9}$/is', $mobile) > 0;
    }

    /**
     * is chinese string or not
     * @param string $str
     * @return bool
     */
    public static function isChinese($str)
    {
        if (!is_string($str)) {
            return false;
        }
        if (!mb_check_encoding($str, 'UTF-8')) {
            $str = mb_convert_encoding($str, 'UTF-8', 'GBK');
        }
        return preg_match('/^[\x{4e00}-\x{9fa5}]+$/uis', $str) > 0;
    }

    /**
     * is english string or not
     * @param string $str
     * @return bool
     */
    public static function isEnglish($str)
    {
        return preg_match('/^[A-Za-z]+$/is', $str) > 0;
    }

    /**
     * is legal account or not
     * @param string $value
     * @param int $min_length
     * @param int $max_length
     * @return bool
     */
    public static function isAccount($value, $min_length = 4, $max_length = 15)
    {
        $regex = "/^[a-zA-Z][a-zA-Z0-9_]{{$min_length},{$max_length}}$/is";
        return preg_match($regex, $value) > 0;
    }

    /**
     * is legal password or not
     * @param string $value
     * @param int $min_length
     * @param int $max_length
     * @return bool
     */
    public static function isPassword($value, $min_length = 4, $max_length = 15)
    {
        $regex = "/^[a-zA-Z][a-zA-Z0-9_]{{$min_length},{$max_length}}$/is";
        return preg_match($regex, $value) > 0;
    }

    /**
     * is telephone number or not
     * @param string $value
     * @return bool
     */
    public static function isTelephone($value)
    {
        return preg_match('/^\d{3}-\d{8}|\d{4}-\d{7}$/is', $value) > 0;
    }

    /**
     * is tencent QQ number or not
     * @param string $value
     * @return bool
     */
    public static function isQQ($value)
    {
        return preg_match('/^[1-9]\d+$/is', $value) > 0;
    }

    /**
     * is ip address or not
     * @param string $value
     * @return bool
     */
    public static function isIP($value)
    {
        return ip2long($value) !== false;
    }

    /**
     * is url or not
     * @param string $value
     * @return bool
     */
    public static function isURL($value)
    {
        return parse_url($value) !== false;
    }

    /**
     * is chinese id number or not
     * @param string $number
     * @return bool
     */
    public static function isIdNumber($number)
    {
        return preg_match('/^\d{15}|\d{18}$/is', $number) > 0;
    }
}