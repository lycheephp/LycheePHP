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
 * Upload tool
 * @author Samding
 * @package Lychee\Utils
 */
class Upload
{
    /**
     * upload file size too big
     * @var int
     */
    const TOO_BIG = 1;

    /**
     * incomplete upload
     * @var int
     */
    const INCOMPLETE = 3;

    /**
     * upload file not exist
     * @var int
     */
    const NOT_EXIST = 4;

    /**
     * incorrect upload file size
     * @var int
     */
    const INCORRECT_SIZE = 5;

    /**
     * upload file info
     * @var array
     */
    private $upload_info;

    /**
     * client side file name
     * @var string
     */
    private $client_side_filename;

    /**
     * file actually mime type
     */
    private $mime;

    /**
     * file actually size
     * @var int
     */
    private $size;

    /**
     * server side file name
     * @var string
     */
    private $server_side_filename;

    /**
     * field name in $_FILE
     * @var string
     */
    private $field_name;

    /**
     * constructor
     * @param $name
     * @throws \Exception
     */
    public function __construct($name)
    {
        $name = trim($name);
        $name = strtolower($name);
        $this->field_name = $name;
        $this->upload_info = isset($_FILES[$name])?$_FILES[$name]:array();
        if (empty($this->upload_info)) {
            throw new \Exception('upload file info not exists');
        }
        $this->client_side_filename = isset($this->upload_info['name'])?$this->upload_info['name']:'';
        $this->size = isset($this->upload_info['size'])?$this->upload_info['size']:0;
        $this->server_side_filename = isset($this->upload_info['tmp_name'])?$this->upload_info['tmp_name']:'';
        if (!empty($this->server_side_filename)) {
            $info = finfo_open(FILEINFO_MIME);
            $this->mime = finfo_file($info, $this->server_side_filename);
            finfo_close($info);
        }
    }

    /**
     * release(unset) upload file
     */
    private function delete()
    {
        unset($_FILES[$this->field_name]);
    }

    /**
     * save upload file
     * @param string $save_path
     * @param string $file_name
     * @param bool $is_cut
     * @return bool
     */
    public function save($save_path, $file_name = '', $is_cut=false)
    {
        if (empty($file_name)) {
            $file_name = $this->client_side_filename;
        }
        if (!is_dir($save_path)) {
            $save_path = dirname($save_path);
        }
        if (!file_exists($save_path)) {
            mkdir($save_path, 0777, true);
        }
        $target = $save_path . DIRECTORY_SEPARATOR . $file_name;
        if ($is_cut) {
            $flag = move_uploaded_file($this->server_side_filename, $target);
        }
        else {
            $flag = copy($this->server_side_filename, $target);
        }
        return $flag;
    }

    /**
     * return upload error
     * @return bool|int
     */
    public function getError()
    {
        $flag = $this->upload_info['error'];
        if ($flag == 0) {
            return false;
        }
        if ($flag == 1 || $flag == 2) {
            $flag = self::TOO_BIG;
        }
        return $flag;
    }

    /**
     * upload is success or not
     * @return bool
     */
    public function isSuccess()
    {
        return !$this->getError();
    }

    /**
     * file is image or not
     */
    public function isImage()
    {
        if ($this->getError() !== true) {
            return true;
        }
        return exif_imagetype($this->server_side_filename) !== false;
    }

    /**
     * file is plain text or not
     */
    public function isPlainText()
    {
        $mime = $this->mime;
        $tmp = explode('/', $mime);
        if (!isset($tmp[0])) {
            return false;
        }
        return strtolower($tmp[0]) == 'text';
    }

    /**
     * return file mime type
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * return client side filename
     * @return string
     */
    public function getClientSideFilename()
    {
        return $this->client_side_filename;
    }

    /**
     * return server side filename
     * @return string
     */
    public function getServerSideFilename()
    {
        return pathinfo($this->server_side_filename, PATHINFO_FILENAME);
    }

    /**
     * return server side file path
     */
    public function getServerSidePath()
    {
        return $this->server_side_filename;
    }

    /**
     * destructor
     */
    public function __destruct()
    {
        $this->delete();
    }
}