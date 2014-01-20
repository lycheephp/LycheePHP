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
        return $this->archive->where(array('archive_id' => $id, 'status' => 1))->select(true);
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
        return $this->archive->where(array('cate_id' => $id))->select(true);
    }

    /**
     * 整理文章分类树
     * @param int $parent_id
     * @param array $list
     * @return array
     */
    private function arrangeCategoryTree($parent_id, array $list)
    {
        $result = array();
        foreach ($list as $info) {
            $temp = $info;
            if ($info['parent_id'] == $parent_id) {
                $temp['children'] = $this->arrangeCategoryTree($temp['cate_id'], $list);
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
        $list = $this->archive->order('sort')->select();
        return $this->arrangeCategoryTree(0, $list);
    }
}