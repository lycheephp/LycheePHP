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
 * 附件上传工具类
 * @author Samding
 * @package Lychee\Attachment
 */
class Upload
{

    /**
     * 文件超出大小
     * @var int
     */
    const TOO_BIG = 1;

    /**
     * 上传不完整
     * @var int
     */
    const INCOMPLETE = 3;

    /**
     * 不正确或者不存在文件
     * @var int
     */
    const NOT_EXIST = 4;

    /**
     * 不存在或者不正确的文件大小
     * @var int
     */
    const INCORRECT_SIZE = 5;

    /**
     * 上传文件信息
     * @var array
     */
    private $file_info;

    /**
     * 客户端文件名
     * @var string
     */
    private $name;

    /**
     * 文件mime类型
     * @var string
     */
    private $mime_type;

    /**
     * 文件大小byte
     * @var int
     */
    private $size;

    /**
     * 服务端临时文件名
     * @var string
     */
    private $tmp_name;

    /**
     * 字段名
     * 上传文件在$_FILES中的key值
     * @var string
     */
    private $field_name;

    /**
     * 构造器
     * @param $name
     * @throws \Exception
     */
    public function __construct($name)
    {
        $name = trim($name);
        $name = strtolower($name);
        $this->field_name = $name;
        $this->file_info = isset($_FILES[$name])?$_FILES[$name]:array();
        if (empty($this->file_info)) {
            throw new \Exception('upload file info not exists');
        }
        $this->name = isset($this->file_info['name'])?$this->file_info['name']:''; //原文件名
        $this->size = isset($this->file_info['size'])?$this->file_info['size']:0; //文件大小
        $this->tmp_name = isset($this->file_info['tmp_name'])?$this->file_info['tmp_name']:'';//文件在服务端的临时名称
        //获取文件mime
        if (!empty($this->tmp_name)) {
            $info = finfo_open(FILEINFO_MIME);
            $this->mime_type = finfo_file($info, $this->tmp_name);
            finfo_close($info);
        }
    }

    /**
     * 释放上传文件
     */
    private function delete()
    {
        unset($_FILES[$this->field_name]);
    }

    /**
     * 保存文件
     * @param string $save_path 保存文件的路径
     * @param string $file_name 保存的文件名 为空则使用原文件名
     * @param bool $is_cut 是否使用剪切方式保存
     * @return bool
     */
    public function save($save_path, $file_name = '', $is_cut=false)
    {
        if (empty($file_name)) {
            $file_name = $this->name;
        }
        if (!is_dir($save_path)) {
            $save_path = dirname($save_path);
        }
        if (!file_exists($save_path)) {
            mkdir($save_path, 0777, true);
        }
        $target = $save_path . DIRECTORY_SEPARATOR . $file_name;
        if ($is_cut) {
            $flag = move_uploaded_file($this->tmp_name, $target);
        }
        else {
            $flag = copy($this->tmp_name, $target);
        }
        return $flag;
    }

    /**
     * 上传是否成功
     * @return bool|int 返回非0则为错误代码
     */
    public function isSuccess()
    {
        $flag = $this->file_info['error'];
        if ($flag == 0) {
            return true;
        }
        if ($flag == 1 || $flag = 2) {
            $flag = self::TOO_BIG;
        }
        return $flag;
    }

    /**
     * 检查上传文件是否为图片
     */
    public function isImage()
    {
        if ($this->isSuccess() !== true) {
            return true;
        }
        return exif_imagetype($this->tmp_name) !== false;
    }

    /**
     * 检查上传文件是否为文本
     */
    public function isText()
    {
        $mime = $this->mime_type;
        $tmp = explode('/', $mime);
        return strtolower($tmp[0]) == 'text';
    }

    /**
     * 获取文件MIME类型
     * @return string
     */
    public function getFileMIME()
    {
        return $this->mime_type;
    }

    /**
     * 获取客户端文件名称
     * @return string
     */
    public function getFileName()
    {
        return $this->name;
    }

    /**
     * 获取服务端临时文件名
     * @return string
     */
    public function getTmpName()
    {
        return pathinfo($this->tmp_name, PATHINFO_FILENAME);
    }

    /**
     * 获取服务端临时文件路径
     */
    public function getTmpPath()
    {
        return $this->tmp_name;
    }

    /**
     * 析构器
     */
    public function __destruct()
    {
        $this->delete();
    }
}