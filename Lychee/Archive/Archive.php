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
namespace Lychee\Archive;

use Lychee\Config as Config;
use Lychee\Base\MySQL\QueryHelper as QueryHelper;

/**
 * 文章模块逻辑类
 * @author Samding
 * @package Lychee\Archive
 */
class Archive
{

    /**
     * 文章表查询类
     * @var QueryHelper
     */
    private $archive;

    /**
     * 文章分类表查询类
     * @var QueryHelper
     */
    private $category;

    /**
     * 构造器
     */
    public function __construct()
    {
        $db_name = Config::get('archive.mysql.db_name');
        $this->archive = new QueryHelper('archive', $db_name);
        $this->category = new QueryHelper('archive_category', $db_name);
    }

    /**
     * 增加文章点击数
     * @param int $id
     * @return int
     */
    public function increaseClick($id)
    {
        $id = intval($id);
        if ($id < 1) {
            return 0;
        }
        return $this->archive->where(array('archive_id' => $id))->increment('click', 1);
    }

    /**
     * 减少文章点击数
     * @param int $id
     * @return int
     */
    public function decreaseClick($id)
    {
        $id = intval($id);
        if ($id < 1) {
            return 0;
        }
        return $this->archive->where(array('archive_id' => $id))->decrement('click', 1);
    }

    /**
     * 获取文章信息
     * @param int $id
     * @return array
     */
    public function getArchiveInfo($id)
    {
        $id = intval($id);
        if ($id < 1) {
            return array();
        }
        return $this->archive->where(array('archive_id' => $id))->select(true);
    }

    /**
     * 根据文章分类获取文章列表
     * @param $cate_id
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getArchiveListByCategory($cate_id, $offset, $limit)
    {
        $offset = intval($offset);
        $limit = intval($limit);
        if ($limit < 1) {
            return array();
        }
        if ($offset < 0) {
            return array();
        }
        return $this->archive->where(array('status' => 1, 'cate_id' => $cate_id))->limit($limit, $offset)
            ->order(array('sort', 'click', 'update_time', 'add_time'))->select();
    }

    /**
     * 获取文章列表
     * @param array $condition
     * @return array
     */
    public function getArchiveList(array $condition = array())
    {
        return $this->archive->where($condition)->order(array('update_time', 'add_time', 'sort'))->select();
    }

    /**
     * 获取文章总数
     * @param array $condition
     * @return int
     */
    public function getArchiveCount(array $condition = array())
    {
        return $this->archive->where($condition)->count();
    }

    /**
     * 获取文章分类信息
     * @param int $id
     * @return array
     */
    public function getCategoryInfo($id)
    {
        $id = intval($id);
        if ($id < 1) {
            return array();
        }
        return $this->category->where(array('cate_id' => $id))->select(true);
    }

    /**
     * 整理文章分类树
     * @param int $parent_id
     * @param array $list
     * @return array
     */
    private static function arrangeCategoryTree($parent_id, array $list)
    {
        $result = array();
        foreach ($list as $info) {
            $temp = $info;
            if ($info['parent_id'] == $parent_id) {
                $temp['children'] = self::arrangeCategoryTree($temp['cate_id'], $list);
                $result[] = $temp;
            }
        }
        return $result;
    }

    /**
     * 以树状图方式取出文章分类
     * return array
     */
    public function getCategoryTree()
    {
        $list = $this->category->order('sort')->select();
        return self::arrangeCategoryTree(0, $list);
    }

    /**
     * 获取分类列表
     * @return array
     */
    public function getCategoryList()
    {
        return $this->category->order('sort')->select();
    }

    /**
     * 编辑分类
     * @param array $data
     * @param int $cate_id
     * @return int
     */
    public function editCategory(array $data, $cate_id)
    {
        $cate_id = intval($cate_id);
        if ($cate_id < 1) {
            return 0;
        }
        return $this->category->data($data)->where(array('cate_id' => $cate_id))->update();
    }

    /**
     * 编辑文章
     * @param array $data
     * @param $archive_id
     * @return int
     */
    public function editArchive(array $data, $archive_id)
    {
        $archive_id = intval($archive_id);
        if ($archive_id < 1) {
            return 0;
        }
        return $this->archive->data($data)->where(array('archive_id' => $archive_id))->update();
    }

    /**
     * 添加文章
     * @param array $data
     * @return int
     */
    public function addArchive(array $data)
    {
        return $this->archive->data($data)->insert();
    }

    /**
     * 添加文章分类
     * @param array $data
     * @return int
     */
    public function addCategory(array $data)
    {
        return $this->category->data($data)->insert();
    }

    /**
     * 审核通过文章
     * @param int $archive_id
     * @return int
     */
    public function passArchive($archive_id)
    {
        $archive_id = intval($archive_id);
        if ($archive_id < 1) {
            return 0;
        }
        //设置状态为1
        return $this->archive->where(array('archive_id' => $archive_id))->data(array('status' => 1))->update();
    }

    /**
     * 待审核or审核不通过文章
     * @param int $archive_id
     * @return int
     */
    public function unpassArchive($archive_id)
    {
        $archive_id = intval($archive_id);
        if ($archive_id < 1) {
            return 0;
        }
        //设置状态为0
        return $this->archive->where(array('archive_id' => $archive_id))->data(array('status' => 0))->update();
    }

    /**
     * 删除文章
     * @param int $archive_id
     * @return int
     */
    public function deleteArchive($archive_id)
    {
        $archive_id = intval($archive_id);
        if ($archive_id < 1) {
            return 0;
        }
        // 设置状态为2
        return $this->archive->where(array('archive_id' => $archive_id))->data(array('status' => 2))->update();
    }

    /**
     * 恢复文章
     * @param int $archive_id
     * @return int
     */
    public function recoverArchive($archive_id)
    {
        $archive_id = intval($archive_id);
        if ($archive_id < 1) {
            return 0;
        }
        //设置状态为0
        return $this->archive->where(array('archive_id' => $archive_id))->data(array('status' => 0))->update();
    }

    /**
     * 删除文章分类
     * @param $cate_id
     * @return int
     */
    public function deleteCategory($cate_id)
    {
        $cate_id = intval($cate_id);
        if ($cate_id < 1) {
            return 0;
        }
        $count = $this->category->where(array('parent_id' => $cate_id))->count();
        if ($count != 0) {
            return 0;
        }
        $condition = array('cate_id' => $cate_id);
        $this->archive->where($condition)->delete();
        return $this->category->where($condition)->delete();
    }
}