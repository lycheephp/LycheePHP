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

use Lychee\Config as Config;
use Lychee\Base\MySQL\QueryHelper as QueryHelper;

/**
 * 附件模块图片逻辑类
 * @author Samding
 * @package Lychee\Attachment
 */
class Image
{

    /**
     * 图片相册表查询类
     * @var QueryHelper
     */
    private $album;

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
        $db_name = Config::get('attachment.mysql.db_name');
        $this->album = new QueryHelper('attachment_album', $db_name);
        $this->image = new QueryHelper('attachment_image', $db_name);
    }

    /**
     * 添加图片相册
     * @param array $data
     * @return int
     */
    public function addAlbum(array $data)
    {
        return $this->album->data($data)->insert();
    }

    /**
     * 编辑图片相册
     * @param array $data
     * @param int $id
     * @return int
     */
    public function editAlbum(array $data, $id)
    {
        $id = intval($id);
        if ($id < 1) {
            return 0;
        }
        return $this->album->data($data)->where(array('album_id' => $id))->update();
    }

    /**
     * 移除相册
     * @param int $id
     * @return int
     */
    public function removeAlbum($id)
    {
        $id = intval($id);
        if ($id < 1) {
            return 0;
        }
        $condition = array('album_id' => $id);
        $this->image->where($condition)->delete();
        return $this->album->where($condition)->delete();
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
     * 获取指定相册信息
     * @param int $id
     * @return array
     */
    public function getAlbumInfo($id)
    {
        $id = intval($id);
        if ($id < 1) {
            return array();
        }
        return $this->album->where(array('album_id' => $id))->select(true);
    }

    /**
     * 获取图片信息
     * @param int $id
     * @return array
     */
    public function getImageInfo($id)
    {
        $id = intval($id);
        if ($id < 1) {
            return array();
        }
        return $this->image->where(array('image_id' => $id))->select(true);
    }

    /**
     * 获取模块默认相册
     * @param string $module_name
     * @return array()
     */
    public function getDefaultAlbumInfo($module_name)
    {
        $module_name = trim($module_name);
        if (empty($module_name)) {
            return array();
        }
        $condition = array('module_name' => $module_name, 'module_id' => 0);
        return $this->album->where($condition)->select(true);
    }

    /**
     * 添加图片至默认模块
     * @param array $data
     * @param string $module_name
     * @return int
     */
    public function addDefaultImage(array $data, $module_name)
    {
        $album_info = $this->getDefaultAlbumInfo($module_name);
        if (empty($album_info)) {
            return 0;
        }
        $album_id = $album_info['album_id'];
        $data['album_id'] = $album_id;
        return $this->image->data($data)->insert();
    }

    /**
     * 对指定模块添加图片
     * @param array $data
     * @param string $module_name
     * @param int $module_id
     * @return int
     */
    public function addModuleImage(array $image_data, $module_name, $module_id)
    {
        $module_name = trim($module_name);
        $module_id = intval($module_id);
        if (empty($module_name)) {
            return 0;
        }
        if ($module_id < 1) {
            return 0;
        }
        $album_info = $this->album->where(array('module_name' => $module_name, 'module_id' => $module_id))->select(true);
        if (empty($album_info)) {
            $data = array();
            $data['module_name'] = $module_name;
            $data['module_id'] = $module_id;
            $data['name'] = '';
            $data['desc'] = '';
            $data['sort'] = 0;
            $data['add_time'] = time();
            $album_id = $this->album->data($data)->insert();
        }
        else {
            $album_id = $album_info['album_id'];
        }
        $image_data['album_id'] = $album_id;
        return $this->image->data($image_data)->insert();
    }
}