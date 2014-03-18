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
 * captcha tool
 * @author Samding
 * @package Lychee\Utils
 */
class Captcha
{
    /**
     * @var int
     */
    const TYPE_LETTER = 1;

    /**
     * @var int
     */
    const TYPE_NUMERIC = 2;

    /**
     * @var int
     */
    const TYPE_HYDRA = 3;

    /**
     * @var int
     */
    const STORE_SESSION = 1;

    /**
     * @var int
     */
    const STORE_COOKIE = 2;

    /**
     * captcha value
     * @var string
     */
    private $captcha;

    /**
     * captcha image handle
     * @var resource
     */
    private $image_handle;

    /**
     *  captcha value length
     * @var int
     */
    private $length;

    /**
     * captcha value type
     * @var int
     */
    private $captcha_type;

    /**
     * captcha info store namespace
     * @var string
     */
    private $namespace;

    /**
     * captcha info store type
     * @var int
     */
    private $store_type;

    /**
     * constructor
     * @param int $length
     * @param int $type
     * @param int $duration
     * @param string $store_namespace
     * @param int $store_type
     */
    public function __construct($length =5 , $type = self::TYPE_HYDRA, $duration = 300,
                                $store_namespace = 'captcha', $store_type = self::STORE_SESSION) {
        $length = intval($length);
        if ($length < 0) {
            $length = 5;
        }
        $this->length = $length ==0?5:$length;
        if (!in_array($type, array(self::TYPE_HYDRA, self::TYPE_LETTER, self::TYPE_NUMERIC))) {
            $type = self::TYPE_HYDRA;
        }
        $this->captcha_type = $type;
        $this->duration = intval($duration);
        $this->namespace = strval($store_namespace);
        if (!in_array($store_type, array(self::STORE_COOKIE, self::STORE_SESSION))) {
            $store_type = self::STORE_SESSION;
        }
        $this->store_type = intval($store_type);
    }

    /**
     * create captcha image and value
     * @return Captcha
     */
    public function create() {
        $this->captcha = $this->generate();
        $this->image_handle = $this->__create();
        return $this;
    }

    /**
     * generate captcha value
     * @return int
     */
    private function generate() {
        $result = '';
        // ASCII: 49 - 57: 1-9
        // ASCII: 65 - 90: A-Za-z
        if ($this->captcha_type == self::TYPE_HYDRA) {
            for ($i = 0; $i < $this->length; $i++) {
                if (mt_rand(0, 1)) {
                    $temp = chr(mt_rand(65, 90));
                    if (mt_rand(0, 1)) {
                        $temp = strtolower($temp);
                    }
                }
                else {
                    $temp = chr(mt_rand(49, 57));
                }
                $result .= $temp;
            }
        }
        elseif ($this->captcha_type == self::TYPE_NUMERIC) {
            for ($i = 0; $i < $this->length; $i++) {
                $temp = chr(mt_rand(49, 57));
                $result .= $temp;
            }
        }
        elseif ($this->captcha_type == self::TYPE_LETTER) {
            for ($i = 0; $i < $this->length; $i++) {
                $temp = chr(mt_rand(65, 90));
                if (mt_rand(0, 1)) {
                    $temp = strtolower($temp);
                }
                $result .= $temp;
            }
        }
        return $result;
    }

    /**
     * create captcha image
     * @return resource
     */
    private function __create() {
        $width = 200;
        $height = 70;
        $image_handle = imagecreatetruecolor($width, $height);
        $background_color = imagecolorallocatealpha($image_handle, 255, 255, 255, 0);
        imagefilledrectangle($image_handle, 0, 0, $width, $height, $background_color);
        $color_list = array(
            imagecolorallocate($image_handle, 35, 102, 147),
            imagecolorallocate($image_handle, 22, 163, 35),
            imagecolorallocate($image_handle, 27, 78, 181),
            imagecolorallocate($image_handle, 214, 36, 7));
        $color = $color_list[mt_rand(0, count($color_list) - 1)];
        $x = 5;
        $size = mt_rand((14 - $this->length) * 4.2, (15 - $this->length) * 4.6);
        $angle = mt_rand(($size - 80) / 2.2, ($size - 80) / 4.2);
        $y = $height - ($height - $size) / 2 - 8;
        for ($i = 0; $i < $this->length; $i++) {
            $str = substr($this->captcha, $i, 1);
            switch (($i + 3) % 3) {
                case 0:
                    break;
                case 1:
                    $x -= 4;
                    $y += 6;
                    break;
                case 2:
                    $x += 4;
                    $y -= 6;
                    break;
                default:
                    break;
            }
            imagettftext($image_handle, $size, $angle, $x, $y, $color, dirname(__FILE__) . '/captcha.ttf', $str);
            $x += $size / 1.5;
        }
        if ($this->length > 4) {
            $x1 = 0.0;
            $y1 = rand(35, 45);
            $yH = 35;
            for ($i = rand(-90, 90); $i < 220; $i += 20) {
                $a = sin($i);
                $x2 = $i;
                $y2 = $yH + $a * 14;
                for ($j = 0; $j <= $this->length; $j += 1) {
                    imageline($image_handle, $x1 + $j, $y1, $x2 + $j, $y2, $color);
                }
                $x1 = $x2;
                $y1 = $y2;
                if ($x1 > $width) {
                    break;
                }
            }
        }
        unset($color_list);
        return $image_handle;
    }

    /**
     * output captcha
     */
    public function display() {
        if (is_null($this->image_handle)) {
            $this->create();
        }
        //save captcha info
        self::saveInfo($this->captcha, $this->duration, $this->namespace, $this->store_type);
        header("Content-type: image/jpeg");
        header("Cache-Control: no-cache");
        header("Pragma: no-cache");
        header("Expires: -1");
        imagejpeg($this->image_handle);
        $this->release();
    }

    /**
     * save captcha image
     * @param string $path
     * @param string $file_name
     */
    public function save($path, $file_name) {
        if (is_null($this->image_handle)) {
            $this->create();
        }
        //save captcha info
        self::saveInfo($this->captcha, $this->duration, $this->namespace, $this->store_type);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $save_path = $path . '/' . $file_name;
        imagejpeg($this->image_handle, $save_path);
        $this->release();
    }

    /**
     * check captcha value
     * @param string $captcha
     * @param string $namespace
     * @param int $store_type
     * @return bool
     */
    public static function check($captcha, $namespace = 'captcha', $store_type = self::STORE_SESSION) {
        $captcha = trim($captcha);
        $captcha = strtolower($captcha);
        if (empty($captcha)) {
            return false;
        }
        $value = self::loadInfo($namespace, $store_type);
        $flag = $captcha == $value;
        if ($flag) {
            self::deleteInfo($namespace, $store_type);
        }
        return $flag;
    }

    /**
     * encrypt captcha info
     * algorithm:3DES
     * @param string $content
     * @return string
     */
    private static function encrypt($content)
    {
        return Encrypt::encrypt($content);
    }

    /**
     * decrypt captcha info
     * algorithm:3DES
     * @param string $content
     * @return string
     */
    private static function decrypt($content)
    {
        return Encrypt::decrypt($content);
    }

    /**
     * save captcha info
     * @param string $value
     * @param int $duration
     * @param string $namespace
     * @param int $store_type
     * @return bool
     */
    private function saveInfo($value, $duration, $namespace, $store_type = self::STORE_SESSION) {
        $data = array();
        $data['captcha'] = $value;
        $data['add_time'] = time();
        $data['duration'] = $duration;
        $temp = serialize($data);
        $content = self::encrypt($temp);
        if ($store_type == self::STORE_COOKIE) {
            setcookie($namespace, $content, 0, '/');
        }
        elseif ($store_type == self::STORE_SESSION) {
            @session_start();
            $_SESSION[$namespace] = $content;
        }
    }

    /**
     * load captcha info
     * @param string $namespace
     * @param int $store_type
     * @return string
     */
    private static function loadInfo($namespace, $store_type = self::STORE_SESSION) {
        $content = '';
        if ($store_type == self::STORE_COOKIE) {
            $content = isset($_COOKIE[$namespace])?$_COOKIE[$namespace]:'';
        }
        elseif ($store_type == self::STORE_SESSION) {
            @session_start();
            $content = isset($_SESSION[$namespace])?$_SESSION[$namespace]:'';
        }
        if (empty($content)) {
            return '';
        }
        $time = time();
        $data = unserialize(self::decrypt($content));
        if ($data['duration'] + $data['add_time'] < $time && $data['duration'] != 0) {
            return '';
        }
        return strtolower($data['captcha']);
    }

    /**
     * delete captcha info
     * @param $namespace
     * @param int $store_type
     */
    private static function deleteInfo($namespace, $store_type = self::STORE_SESSION) {
        if ($store_type == self::STORE_COOKIE && isset($_COOKIE[$namespace])) {
            setcookie($namespace, null, time() - 3600);
        }
        elseif ($store_type == self::STORE_SESSION && isset($_SESSION[$namespace])) {
            @session_start();
            unset($_SESSION[$namespace]);
        }
    }

    /**
     * release resource
     */
    private function release() {
        if (is_resource($this->image_handle)) {
            imagedestroy($this->image_handle);
        }
    }

    /**
     * destructor
     */
    public function __destruct() {
        $this->release();
    }
}