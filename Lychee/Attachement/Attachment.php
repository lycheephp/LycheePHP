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
        return $this->image_albumn->data($data)->insert();
    }

    /**
     * 编辑图片相册
     * @param array $data
     * @param int $id
     * @return int
     */
    public function editAlbumn(array $data, $id)
    {
        $id = intval($id);
        if ($id < 1) {
            return 0;
        }
        return $this->image_albumn->data($data)->where(array('albumn_id' => $id))->update();
    }

    /**
     * 移除相册
     * @param int $id
     * @return int
     */
    public function removeAlbumn($id)
    {
        $id = intval($id);
        if ($id < 1) {
            return 0;
        }
        return $this->image_albumn->where(array('albumn_id' => $id))->delete();
    }

    /**
     * 添加图片
     * @param array $data
     * @return int
     */
    public function addImage(array $data)
    {
        return $this->image->data($data)->insert();
    }

    /**
     * 编辑图片
     * @param array $data
     * @param int $id
     * @return int
     */
    public function editImage(array $data, $id)
    {
        $id = intval($id);
        if ($id < 1) {
            return 0;
        }
        return $this->image->where(array('image_id' => $id))->data($data)->update();
    }

    /**
     * 移除图片
     * @param int $id
     * @return int
     */
    public function removeImage($id)
    {
        $id = intval($id);
        if ($id < 1) {
            return 0;
        }
        return $this->image->where(array('image_id' => $id))->delete();
    }

    /**
     * 添加文件
     * @param array $data
     * @return int
     */
    public function addFile(array $data)
    {
        return $this->file->data($data)->insert();
    }

    /**
     * 编辑文件
     * @param array $data
     * @param int $id
     * @return int
     */
    public function editFile(array $data, $id)
    {
        $id = intval($id);
        if ($id < 1) {
            return 0;
        }
        return $this->file->data($data)->where(array('file_id' => $id))->update();
    }

    /**
     * 移除文件
     * @param int $id
     * @return int
     */
    public function removeFile($id)
    {
        $id = intval($id);
        if ($id < 1) {
            return 0;
        }
        return $this->file->where(array('file_id' => $id))->delete();
    }

    /**
     * 开始下载文件
     * @param int $id
     */
    public function download($id)
    {

    }

    /**
     * 获取指定相册信息
     * @param int $id
     * @return array
     */
    public function getAlbumnInfo($id)
    {

    }

    /**
     * 获取文件信息
     * @param int $id
     * @return array
     */
    public function getFileInfo($id)
    {

    }

    /**
     * 获取图片信息
     * @param int $id
     * @return array
     */
    public function getImageInfo($id)
    {

    }

    /**
     * 获取指定相册图片列表
     * @param int $id
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getAlbumnImageList($id, $offset, $limit)
    {

    }
}