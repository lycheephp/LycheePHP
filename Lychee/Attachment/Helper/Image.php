<?php
/**
 * Copyright 2013 henryzengpn(Henryzeng) koboshi(Samding)
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
namespace Lychee\Attachment\Helper;

/**
 * 图片处理工具类
 * @author Samding
 * @package Lychee\Attachment\Helper
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
     * 请使用Image::load方法
     */
    private function __construct($file_path) {
        if (!file_exists($file_path)) {
            throw new \Exception('file not exist');
        }
        //判断文件是否为图片
        $flag = self::getImageType($file_path);
        if (!$flag) {
            throw new \Exception('invalid file type!', 1);
        }
        $this->image_path = $file_path;
        //打开文件
        $handle = false;
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
        if (!is_resource($handle)) {
            throw new \Exception('unknow error!', 1);
        }
        $this->image_handle = $handle;
    }

    /**
     * 判断文件是否为图片
     * @param string $file_path
     * @return bool
     */
    public static function isImage($file_path) {
        if (file_exists($file_path)) {
            $flag = exif_imagetype($file_path);
            return $flag !== false;
        }
        return false;
    }

    /**
     * 获取图片类型
     * @param string $file_path
     * @return int|bool
     */
    public static function getImageType($file_path) {
        if (self::isImage($file_path)) {
            $type = exif_imagetype($file_path);
            $result = false;
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
            return $result;
        }
        return false;
    }

    /**
     * 加载图片
     * @param string $file_path
     * @return Image
     */
    public static function load($file_path) {
        $image = new Image($file_path);
        return $image;
    }

    /**
     * 图片旋转
     * 顺时针
     * @param int $degree
     * @return Image
     */
    public function rotate($degree) {
        $degree = intval($degree);
        if ($degree == 0 || $degree == 360) {
            return $this;
        }
        if ($degree > 360) {
            $degree = $degree % 360;
        }
        $degree *= -1;
        $this->image_handle = imagerotate($this->image_handle, $degree, 0);
        return $this;
    }

    /**
     * 图像翻转
     * @param bool $is_y 为真时图像y轴翻转
     * @return Image
     */
    public function flip($is_y = true) {
        $width = imagesx($this->image_handle);
        $height = imagesy($this->image_handle);
        $target_handle = imagecreatetruecolor($width, $height);
        if ($is_y) {
            //y轴旋转
            for ($x = 0; $x < $width; $x++) {
                imagecopy($target_handle, $this->image_handle, ($width - 1) - $x, 0, $x, 0, 1, $height);
            }
        }
        else {
            //x轴旋转
            for ($y = 0; $y < $height; $y++) {
                imagecopy($target_handle, $this->image_handle, 0, ($height - 1) - $y, 0, $y, $width, 1);
            }
        }
        $this->image_handle = $target_handle;
        return $this;
    }

    /**
     * 添加水印
     * 默认位置左上
     * @param string $watermark_path
     * @param int $pos
     * @param int $x_offset
     * @param int $y_offset
     * @throws \Exception
     * @return Image
     */
    public function watermark($watermark_path, $pos = self::POS_TOP_LEFT, $x_offset = 0, $y_offset = 0) {
        //读取水印文件
        if (!file_exists($watermark_path)) {
            throw new \Exception('watermark file not exist', 1);
        }
        $type = self::getImageType($watermark_path);
        $wm_handle = null;
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
        if (!is_resource($wm_handle)) {
            throw new \Exception('unknow error!', 1);
        }
        $wm_width = imagesx($wm_handle);
        $wm_height = imagesy($wm_handle);

        //读取图片
        $source_width = imagesx($this->image_handle);
        $source_height = imagesy($this->image_handle);

        if ($source_width < $wm_width || $source_height < $wm_height) {
            throw new \Exception('watermark is too big');//水印比图片大
        }

        //确定位置
        $dst_x = 0;
        $dst_y = 0;
        if ($pos == self::POS_TOP_RIGHT) {
            //右上
            $dst_x = $source_width - $wm_width;
        }
        elseif ($pos == self::POS_TOP_CENTER) {
            //顶部居中
            $dst_x = ($source_width - $wm_width) / 2;
        }
        elseif ($pos == self::POS_BOTTOM_LEFT) {
            //左下
            $dst_y = $source_height - $wm_height;
        }
        elseif ($pos == self::POS_BOTTOM_RIGHT) {
            //右下
            $dst_x = $source_width - $wm_width;
            $dst_y = $source_height - $wm_height;
        }
        elseif ($pos == self::POS_BOTTOM_CENTER) {
            //底部居中
            $dst_x = ($source_width - $wm_width) / 2;
            $dst_y = $source_height - $wm_height;
        }
        elseif ($pos == self::POS_MIDDLE_CENTER) {
            //居中
            $dst_x = ($source_width - $wm_width) / 2;
            $dst_y = ($source_height - $wm_height) / 2;
        }
        $dst_x += $x_offset;
        $dst_y += $y_offset;

        //处理
        imagealphablending($this->image_handle, true);
        imagecopy($this->image_handle, $wm_handle, $dst_x, $dst_y, 0, 0, $wm_width, $wm_height);

        return $this;
    }

    /**
     * resize图片
     * @param int $width
     * @param int $height
     * @param bool $is_scale 为真时保持比例，忽略高度参数
     * @return Image
     */
    public function resize($width, $height, $is_scale = true) {
        $source_width = imagesx($this->image_handle);//宽度
        $source_height = imagesy($this->image_handle);//高度

        //确定目标文件尺寸
        $target_width = $width;
        $target_height = $height;
        if ($is_scale) {
            //以宽为基础等比例
            $target_height = ($target_width / $source_width) * $source_height;
        }

        //缩放
        $target_handle = imagecreatetruecolor($target_width, $target_height);
        imagecopyresampled($target_handle, $this->image_handle, 0, 0, 0, 0,
            $target_width, $target_height, $source_width, $source_height);
        $this->image_handle = $target_handle;
        //返回
        return $this;
    }

    /**
     * 保存结果至路径
     * 仅支持jpeg和png
     * 默认保存为png格式
     * @param string $path
     * @param string $file_name
     * @param int $type
     * @throws \Exception
     * @return Image
     */
    public function save($path, $file_name, $type = self::TYPE_PNG) {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $save_path = $path . '/' . $file_name;
        if ($type == self::TYPE_JPEG) {
            imagejpeg($this->image_handle, $save_path);
        }
        elseif ($type == self::TYPE_PNG) {
            imagepng($this->image_handle, $save_path);
        }
        else {
            throw new \Exception('not supported file type');
        }
        return $this;
    }

    /**
     * 输出图像至标准输出流
     */
    public function display() {
        header("Content-type: image/jpeg");
        imagejpeg($this->image_handle);
    }

    /**
     * 析构器
     */
    public function __destruct() {
        imagedestroy($this->image_handle);
    }
}