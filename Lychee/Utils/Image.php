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
 * Image tool
 * @author Samding
 * @package Lychee\Utils
 */
class Image {

    /**
     * 左上角
     * @var int
     */
    const POS_TOP_LEFT = 1;

    /**
     * 右上角
     * @var int
     */
    const POS_TOP_RIGHT = 2;

    /**
     * 左下角
     * @var int
     */
    const POS_BOTTOM_LEFT = 3;

    /**
     * 右下角
     * @var int
     */
    const POS_BOTTOM_RIGHT = 4;

    /**
     * 居中
     * @var int
     */
    const POS_MIDDLE_CENTER = 5;

    /**
     * 顶端居中
     * @var int
     */
    const POS_TOP_CENTER = 6;

    /**
     * 底端居中
     * @var int
     */
    const POS_BOTTOM_CENTER = 7;

    /**
     * PNG类型
     * @var int
     */
    const TYPE_PNG = 1;

    /**
     * BMP类型
     * @var int
     */
    const TYPE_BMP = 2;

    /**
     * JPEG类型
     * @var int
     */
    const TYPE_JPEG = 3;

    /**
     * GIF类型
     * @var int
     */
    const TYPE_GIF = 4;

    /**
     * 源图片路径
     * @var string
     */
    private $image_path;

    /**
     * 图片句柄
     * @var Image
     */
    private $image_handle;

    /**
     * constructor
     * @throws \Exception
     * @param string $file_path
     */
    public function __construct($file_path)
    {
        if (!file_exists($file_path)) {
            throw new \Exception('file not exist');
        }
        $flag = self::getImageType($file_path);
        if (!$flag) {
            throw new \Exception('invalid file type', 1);
        }
        $this->image_path = $file_path;
        if ($flag == self::TYPE_BMP) {
            $handle = imagecreatefromwbmp($file_path);
        }
        elseif ($flag == self::TYPE_GIF) {
            $handle = imagecreatefromgif($file_path);
        }
        elseif ($flag == self::TYPE_JPEG) {
            $handle = imagecreatefromjpeg($file_path);
        }
        elseif ($flag == self::TYPE_PNG) {
            $handle = imagecreatefrompng($file_path);
        }
        else {
            throw new \Exception('unsupported image type', 1);
        }
        if ($handle === false) {
            throw new \Exception('can not open image', 1);
        }
        $this->image_handle = $handle;
        imagesavealpha($this->image_handle, true);
    }

    /**
     * detect file is image or not
     * @param string $file_path
     * @return bool
     */
    public static function isImage($file_path)
    {
        if (file_exists($file_path)) {
            $flag = exif_imagetype($file_path);
            return $flag !== false;
        }
        return false;
    }

    /**
     * detect image type
     * @param string $file_path
     * @return int
     */
    public static function getImageType($file_path)
    {
        $result = false;
        if (self::isImage($file_path)) {
            $type = exif_imagetype($file_path);
            if ($type == IMAGETYPE_BMP) {
                $result = self::TYPE_BMP;
            }
            elseif ($type == IMAGETYPE_PNG) {
                $result = self::TYPE_PNG;
            }
            elseif ($type == IMAGETYPE_JPEG) {
                $result = self::TYPE_JPEG;
            }
            elseif ($type == IMAGETYPE_GIF) {
                $result = self::TYPE_GIF;
            }
        }
        return $result;
    }

    /**
     * rotate image
     * @param int $degree clockwise rotate degree
     * @return Image
     */
    public function rotate($degree)
    {
        $degree = intval($degree);
        if ($degree < 0) {
            if ($degree < -360) {
                $degree = $degree % 360;
            }
            $degree = 360 - abs($degree);
        }
        if ($degree == 0 || $degree == 360) {
            return $this;
        }
        if ($degree > 360) {
            $degree = $degree % 360;
        }
        $degree *= -1;
        $this->image_handle = imagerotate($this->image_handle, $degree, 0xffffff);
        return $this;
    }

    /**
     * image flip
     * @param bool $is_y base on Y Axis instead of X Axis
     * @return Image
     */
    public function flip($is_y = false)
    {
        $width = imagesx($this->image_handle);
        $height = imagesy($this->image_handle);
        $target_handle = imagecreatetruecolor($width, $height);
        imagealphablending($target_handle, false);
        imagesavealpha($target_handle, true);
        if ($is_y) {
            for ($x = 0; $x < $width; $x++) {
                imagecopy($target_handle, $this->image_handle, ($width - 1) - $x, 0, $x, 0, 1, $height);
            }
        }
        else {
            for ($y = 0; $y < $height; $y++) {
                imagecopy($target_handle, $this->image_handle, 0, ($height - 1) - $y, 0, $y, $width, 1);
            }
        }
        $this->image_handle = $target_handle;
        return $this;
    }

    /**
     * add watermark to image
     * @throws \Exception
     * @param string $watermark_path
     * @param int $pos watermark position default is TOP_LEFT
     * @param int $x_offset
     * @param int $y_offset
     * @return Image
     */
    public function watermark($watermark_path, $pos = self::POS_TOP_LEFT, $x_offset = 0, $y_offset = 0)
    {
        // read watermark file
        if (!file_exists($watermark_path)) {
            throw new \Exception('watermark file not exist', 1);
        }
        $type = self::getImageType($watermark_path);
        $wm_handle = false;
        if ($type == self::TYPE_BMP) {
            $wm_handle = imagecreatefromwbmp($watermark_path);
        }
        elseif ($type == self::TYPE_GIF) {
            $wm_handle = imagecreatefromgif($watermark_path);
        }
        elseif ($type == self::TYPE_JPEG) {
            $wm_handle = imagecreatefromjpeg($watermark_path);
        }
        elseif ($type == self::TYPE_PNG) {
            $wm_handle = imagecreatefrompng($watermark_path);
        }
        if ($wm_handle === false) {
            throw new \Exception('can not open watermark file', 1);
        }
        $wm_width = imagesx($wm_handle);
        $wm_height = imagesy($wm_handle);
        // open image
        $source_width = imagesx($this->image_handle);
        $source_height = imagesy($this->image_handle);

        if ($source_width < $wm_width || $source_height < $wm_height) {
            throw new \Exception('watermark size bigger than image');//水印比图片大
        }
        // decide position
        $dst_x = 0;
        $dst_y = 0;
        if ($pos == self::POS_TOP_RIGHT) {
            $dst_x = $source_width - $wm_width;
        }
        elseif ($pos == self::POS_TOP_CENTER) {
            $dst_x = ($source_width - $wm_width) / 2;
        }
        elseif ($pos == self::POS_BOTTOM_LEFT) {
            $dst_y = $source_height - $wm_height;
        }
        elseif ($pos == self::POS_BOTTOM_RIGHT) {
            $dst_x = $source_width - $wm_width;
            $dst_y = $source_height - $wm_height;
        }
        elseif ($pos == self::POS_BOTTOM_CENTER) {
            $dst_x = ($source_width - $wm_width) / 2;
            $dst_y = $source_height - $wm_height;
        }
        elseif ($pos == self::POS_MIDDLE_CENTER) {
            $dst_x = ($source_width - $wm_width) / 2;
            $dst_y = ($source_height - $wm_height) / 2;
        }
        // attach watermark
        $dst_x += $x_offset;
        $dst_y += $y_offset;
        imagealphablending($this->image_handle, true);
        imagecopy($this->image_handle, $wm_handle, $dst_x, $dst_y, 0, 0, $wm_width, $wm_height);

        return $this;
    }

    /**
     * resize image
     * @param int $width
     * @param int $height
     * @param bool $keep_scale
     * @return Image
     */
    public function resize($width, $height, $keep_scale = true)
    {
        $source_width = imagesx($this->image_handle);//宽度
        $source_height = imagesy($this->image_handle);//高度

        // detect image size
        $target_width = $width;
        $target_height = $height;
        if ($keep_scale) {
            // keep scale
            $target_height = ($target_width / $source_width) * $source_height;
        }

        // resize
        $target_handle = imagecreatetruecolor($target_width, $target_height);
        imagealphablending($target_handle, false);
        imagesavealpha($target_handle, true);
        imagecopyresampled($target_handle, $this->image_handle, 0, 0, 0, 0,
            $target_width, $target_height, $source_width, $source_height);
        $this->image_handle = $target_handle;
        return $this;
    }

    /**
     * save image file
     * @throws \Exception
     * @param string $path
     * @param string $file_name
     * @param int $type
     * @return Image
     */
    public function save($path, $file_name, $type = 0)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $save_path = $path . '/' . $file_name;
        if (empty($type)) {
            $type = self::getImageType($this->image_path);
        }
        if ($type == self::TYPE_JPEG) {
            imagejpeg($this->image_handle, $save_path, 85);
        }
        elseif ($type == self::TYPE_PNG) {
            imagepng($this->image_handle, $save_path);
        }
        elseif ($type == self::TYPE_GIF) {
            imagegif($this->image_handle, $save_path);
        }
        elseif ($type == self::TYPE_BMP) {
            imagejpeg($this->image_handle, $save_path, 85);
        }
        else {
            throw new \Exception('unsupported file type');
        }
        return $this;
    }

    /**
     * output captcha
     */
    public function display()
    {
        header("Content-type: image/png");
        imagepng($this->image_handle);
    }

    /**
     * destructor
     */
    public function __destruct()
    {
        if (is_resource($this->image_handle)) {
            imagedestroy($this->image_handle);
        }
    }
}