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

use Lychee\Base\MySQL\QueryHelper as QueryHelper;

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
        $this->archive = new QueryHelper('archive');
        $this->category = new QueryHelper('archive_category');
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
    public function addCategry(array $data)
    {
        return $this->category->data($data)->insert();
    }

    /**
     * 编辑文章
     * @param array $data
     * @param int $id
     * @return int
     */
    public function editArchive(array $data, $id)
    {
        $id = intval($id);
        if ($id < 1) {
            return 0;
        }
        return $this->archive->where(array('archive_id' => $id))->data($data)->update();
    }

    /**
     * 编辑文章分类
     * @param array $data
     * @param int $id
     * @return int
     */
    public function editCategory(array $data, $id)
    {
        $id = intval($id);
        if ($id < 1) {
            return 0;
        }
        return $this->category->where(array('cate_id' => $id))->data($data)->update();
    }

    /**
     * 删除文章
     * @param int $id
     * @return int
     */
    public function deleteArchive($id)
    {
        $id = intval($id);
        if ($id < 1) {
            return 0;
        }
        return $this->archive->where(array('archive_id' => $id))->delete();
    }

    /**
     * 删除文章分类
     * @param int $id
     * @return int
     */
    public function deleteCategory($id)
    {
        $id = intval($id);
        if ($id < 1) {
            return 0;
        }
        return $this->category->where(array('cate_id' => $id))->delete();
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
}