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
namespace Lychee\Order;

use Lychee\Config as Config;
use Lychee\Base\MySQL\QueryHelper as QueryHelper;
use Lychee\Base\MySQL\Operator as Operator;

/**
 * 订单模块商品逻辑类
 * @author Samding
 * @package Lychee\Order;
 */
class Goods
{

    /**
     * 产品分类表查询类
     * @var QueryHelper
     */
    private $category;

    /**
     * 产品表查询类
     * @var QueryHelper
     */
    private $goods;

    /**
     * 产品属性表查询类
     * @var QueryHelper
     */
    private $attribute;

    /**
     * 构造器
     */
    public function __construct()
    {
        $db_name = Config::get('order.mysql.db_name');
        $this->goods = new QueryHelper('order_goods', $db_name);
        $this->category = new QueryHelper('order_goods_category', $db_name);
        $this->attribute = new QueryHelper('order_goods_attribute', $db_name);
    }

    /**
     * 增加库存
     * @param int $goods_id
     * @param int $num
     * @return int
     */
    public function increaseStock($goods_id, $num)
    {
        $goods_id = intval($goods_id);
        if ($goods_id < 1) {
            return 0;
        }
        $num = intval($num);
        if ($num < 1) {
            return 0;
        }
        return $this->goods->where(array('goods_id' => $goods_id))->increment('stock', $num);
    }

    /**
     * 减少库存
     * @param int $goods_id
     * @param int $num
     * @return int
     */
    public function decreaseStock($goods_id, $num)
    {
        $goods_id = intval($goods_id);
        if ($goods_id < 1) {
            return 0;
        }
        $num = intval($num);
        if ($num < 1) {
            return 0;
        }
        return $this->goods->where(array('goods_id' => $goods_id, 'stock' => array(Operator::QUERY_GTE => $num)))->decrement('stock', $num);
    }

    /**
     * 增加文章点击数
     * @param int $goods_id
     * @return int
     */
    public function increaseClick($goods_id)
    {
        $goods_id = intval($goods_id);
        if ($goods_id < 1) {
            return 0;
        }
        return $this->goods->increment('click', 1);
    }

    /**
     * 减少文章点击数
     * @param int $goods_id
     * @return int
     */
    public function decreaseClick($goods_id)
    {
        $goods_id = intval($goods_id);
        if ($goods_id < 1) {
            return 0;
        }
        return $this->goods->decrement('click', 1);
    }

    /**
     * 获取商品信息
     * @param int $goods_id
     * @return array
     */
    public function getGoodsInfo($goods_id)
    {
        $goods_id = intval($goods_id);
        if ($goods_id < 1) {
            return 0;
        }
        return $this->goods->where(array('goods_id' => $goods_id))->select(true);
    }

    /**
     * 根据分类获取商品列表
     * @param int $cate_id
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getGoodsListByCategory($cate_id, $offset, $limit)
    {
        $cate_id = intval($cate_id);
        $offset = intval($offset);
        $limit = intval($limit);
        if ($limit < 1) {
            return array();
        }
        if ($offset < 0) {
            return array();
        }
        return $this->goods->where(array('cate_id' => $cate_id, 'status' => 1))->limit($limit, $offset)
            ->order(array('sort', 'click', 'update_time', 'add_time'))->select();
    }

    /**
     * 根据分类获取商品总数
     * @param $cate_id
     * @return int
     */
    public function getGoodsCountByCategory($cate_id)
    {
        $cate_id = intval($cate_id);
        return $this->goods->where(array('cate_id' => $cate_id, 'status' => 1))->count();
    }

    /**
     * 获取商品列表
     * @param array $condition
     * @return array
     */
    public function getGoodsList(array $condition = array())
    {
        return $this->goods->where($condition)->order(array('sort', 'click', 'update_time', 'add_time'))->select();
    }

    /**
     * 获取商品总数
     * @param array $condition
     * @return int
     */
    public function getGoodsCount(array $condition = array())
    {
        return $this->goods->where($condition)->order(array('sort', 'click', 'update_time', 'add_time'))->count();
    }

    /**
     * 获取分类信息
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
     * 整理商品分类树
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
     * 以树状图方式取出商品分类
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
     * 编辑商品分类
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
     * 添加商品分类
     * @param array $data
     * @return int
     */
    public function addCategory(array $data)
    {
        return $this->category->data($data)->insert();
    }

    /**
     * 编辑商品
     * @param array $data
     * @param $goods_id
     * @return int
     */
    public function editGoods(array $data, $goods_id)
    {
        $goods_id = intval($goods_id);
        if ($goods_id < 1) {
            return 0;
        }
        return $this->goods->data($data)->where(array('goods_id' => $goods_id))->update();
    }

    /**
     * 添加商品
     * @param array $data
     * @return int
     */
    public function addGoods(array $data)
    {
        return $this->goods->data($data)->insert();
    }

    /**
     * 商品上架
     * @param int $goods_id
     * @return int
     */
    public function shelveGoods($goods_id)
    {
        $goods_id = intval($goods_id);
        if ($goods_id < 1) {
            return 0;
        }
        return $this->goods->where(array('goods_id' => $goods_id))->data(array('status' => 1))->update();
    }

    /**
     * 商品下架
     * @param int $goods_id
     * @return int
     */
    public function offShelveGoods($goods_id)
    {
        $goods_id = intval($goods_id);
        if ($goods_id < 1) {
            return 0;
        }
        return $this->goods->where(array('goods_id' => $goods_id))->data(array('status' => 0))->update();
    }

    /**
     * 添加商品属性
     * @param string $name
     * @param string $value
     * @param int $goods_id
     * @return int
     */
    public function addGoodsAttribute($name, $value, $goods_id)
    {
        $goods_id = intval($goods_id);
        if ($goods_id < 1) {
            return 0;
        }
        $data = array();
        $data['name'] = $name;
        $data['value'] = $value;
        $data['goods_id'] = $goods_id;
        return $this->attribute->data($data)->insert();
    }

    /**
     * 删除商品属性
     * @param int $goods_id
     * @return int
     */
    public function deleteGoodsAttribute($goods_id)
    {
        $goods_id = intval($goods_id);
        if ($goods_id < 1) {
            return 0;
        }
        return $this->attribute->where(array('goods_id' => $goods_id))->delete();
    }

    /**
     * 编辑商品属性
     * @param array $data
     * @param int $goods_id
     * @return int
     */
    public function editGoodsAttribute(array $data, $goods_id)
    {
        $goods_id = intval($goods_id);
        if ($goods_id < 1) {
            return 0;
        }
        $this->deleteGoodsAttribute($goods_id);
        $flag = 0;
        foreach ($data as $name => $value) {
             $flag += $this->addGoodsAttribute($name, $value, $goods_id);
        }
        return $flag;
    }

    /**
     * 删除商品
     * @param int $goods_id
     * @return int
     */
    public function deleteGoods($goods_id)
    {
        $goods_id = intval($goods_id);
        if ($goods_id < 1) {
            return 0;
        }
        $this->deleteGoodsAttribute($goods_id);
        return $this->goods->where(array('goods_id' => $goods_id))->delete();
    }

    /**
     * 删除分类
     * @param int $cate_id
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
        $goods_list = $this->goods->where($condition)->field('goods_id')->select();
        foreach ($goods_list as $row) {
            $this->deleteGoods($row['goods_id']);
        }
        return $this->category->where($condition)->delete();
    }
}
