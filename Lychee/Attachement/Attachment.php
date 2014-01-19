<?php
/**
 * Copyright 2013 henryzengpn koboshi
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
namespace Lychee\Attachment;

use Lychee\Base\MySQL\QueryHelper as QueryHelper;

/**
 * 附件模块逻辑类
 * @author Samding
 * @package Lychee\Attachment
 */
class Attachment
{

    /**
     * 图片相册表查询类
     * @var QueryHelper
     */
    private $image_albumn;

    /**
     * 文件表查询类
     * @var QueryHelper
     */
    private $file;

    /**
     * 图片表查询类
     * @var QueryHelper
     */
    private $image;

    /**
     * 构造器
     */
    public function __construct()
    {
        $this->image_albumn = new QueryHelper('attachment_image_albumn');
        $this->image = new QueryHelper('attachment_image');
        $this->file = new QueryHelper('attachment_file');
    }

    /**
     * 添加图片相册
     * @param array $data
     * @return int
     */
    public function addAlbumn(array $data)
    {

    }

    /**
     * 编辑图片相册
     * @param array $data
     * @param int $id
     * @return int
     */
    public function editAlbumn(array $data, $id)
    {

    }

    /**
     * 移除相册
     * @param int $id
     * @return int
     */
    public function removeAlbumn($id)
    {

    }

    /**
     * 添加图片
     * @param array $data
     * @return int
     */
    public function addImage(array $data)
    {

    }

    /**
     * 编辑图片
     * @param array $data
     * @param int $id
     * @return int
     */
    public function editImage(array $data, $id)
    {

    }

    /**
     * 移除图片
     * @param int $id
     * @return int
     */
    public function removeImage($id)
    {

    }

    /**
     * 添加文件
     * @param array $data
     * @return int
     */
    public function addFile(array $data)
    {

    }

    /**
     * 编辑文件
     * @param array $data
     * @param int $id
     * @return int
     */
    public function editFile(array $data, $id)
    {

    }

    /**
     * 移除文件
     * @param int $id
     * @return int
     */
    public function removeFile($id)
    {

    }
}