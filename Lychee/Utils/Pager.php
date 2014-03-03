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
 * Pager tool
 * @author Samding
 * @package Lychee\Utils
 */
class Pager
{

    /**
     * 当前页码
     * @var int
     */
    private $page;

    /**
     * 总记录数
     * @var int
     */
    private $total;

    /**
     * 页尺寸
     * @var int
     */
    private $size;

    /**
     * 页码键值
     * @var string
     */
    private $page_key;

    /**
     * 分页查询参数
     * @var array
     */
    private $query_param;

    /**
     * 构造器
     * @param int $page
     * @param int $total
     * @param int $size
     * @param string $page_key
     */
    public function __construct($page, $total, $size = 30, $page_key = 'page')
    {
        $this->page = $page;
        $this->total = $total;
        $this->size = $size;
        $this->page_key = $page_key;
    }

    /**
     * 获取总记录数
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * 设置总记录数
     * @param int $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * 获取当前页码
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * 设置当前页码
     * @param int $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * 获取页尺寸
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * 设置页尺寸
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * 获取总页数
     * @return int
     */
    public function getTotalPage()
    {
        $tmp = floatval($this->total) / floatval($this->size);
        return ceil($tmp);
    }

    /**
     * 获取数据行偏移值
     * @return int
     */
    public function getOffset()
    {
        if ($this->page < 1) {
            $this->page = 1;
        }
        $total_page = $this->getTotalPage();
        if ($this->page > $total_page) {
            $this->page = $total_page;
        }
        $result = ($this->page - 1) * $this->size;
        return intval($result);
    }

    /**
     * 获取数据行数量
     * @return int
     */
    public function getLimit()
    {
        return $this->size;
    }

    /**
     * 获取数据查询的LIMIT子句
     * @return string
     */
    public function getLimitStr()
    {
        $offset = $this->getOffset();
        $limit = $this->getLimit();
        return "{$offset}, {$limit}";
    }

    /**
     * 设置参数
     */
    public function setQueryParam(array $params)
    {
        $this->query_param = $params;
    }

    /**
     * 获取页面跳转URL
     * @param int $page
     * @return string
     */
    public function getPageURL($page)
    {
        $total_page = $this->getTotalPage();
        if ($page < 1) {
            $page = 1;
        }
        elseif ($page > $total_page) {
            $page = $total_page;
        }
        $path = HTTP::getURLPath();
        $output = array();
        foreach ($this->query_param as $key => $value) {
            $value = urlencode($value);
            $output[] = "{$key}={$value}";
        }
        $output[] = "{$this->page_key}={$page}";
        $query_str = implode('&', $output);
        return $path . '?' . $query_str;
    }

    /**
     * 获取要显示的页码列表
     * @param int $size
     * @return array
     */
    public function getPageList($size = 5)
    {
        $total_page = $this->getTotalPage();
        $current_page = $this->getPage();
        if ($size % 2 == 0) {
            $size += 1;
        }
        $margin_size = ($size - 1) / 2;
        $start_num = $current_page - $margin_size;
        $end_num = $current_page + $margin_size;
        if ($start_num < 1) {
            $start_num = 1;
        }
        if ($end_num > $total_page) {
            $end_num = $total_page;
        }
        return range($start_num, $end_num);
    }
}