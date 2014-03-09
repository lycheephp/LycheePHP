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

/**
 * 订单模块逻辑类
 * @author Samding
 * @package Lychee\Order
 */
class Order
{

    /**
     * 订单建立前
     */
    const BEFORE_CREATE = 'before_create';

    /**
     * 订单建立后
     */
    const AFTER_CREATE = 'after_create';

    /**
     * 订单确认前
     */
    const BEFORE_CONFIRM = 'before_confirm';

    /**
     * 订单确认后
     */
    const AFTER_CONFIRM = 'after_confirm';

    /**
     * 订单支付前
     */
    const BEFORE_PAY = 'before_pay';

    /**
     * 订单支付后
     */
    const AFTER_PAY = 'after_pay';

    /**
     * 订单完成前
     */
    const BEFORE_COMPLETE = 'before_complete';

    /**
     * 订单完成后
     */
    const AFTER_COMPLETE = 'after_complete';

    /**
     * 订单表查询类
     * @var QueryHelper
     */
    private $order;

    /**
     * 订单商品表查询类
     * @var QueryHelper
     */
    private $goods;

    /**
     * 构造器
     */
    public function __construct()
    {
        $db_name = Config::get('order.mysql.db_name');
        $this->order = new QueryHelper('order', $db_name);
        $this->goods = new QueryHelper('order_goods', $db_name);
    }

    /**
     * 获取订单信息
     * @param int $order_id
     * @return array
     */
    public function getOrderInfo($order_id)
    {
        $order_id = intval($order_id);
        if ($order_id < 1) {
            return array();
        }
        return $this->order->where(array('order_id' => $order_id))->select(true);
    }

    /**
     * 获取订单的产品信息
     * @param int $order_id
     * @return array
     */
    public function getOrderGoodsInfo($order_id)
    {
        //todo
    }

    /**
     * 获取订单列表
     * @param array $condition
     * @return array
     */
    public function getOrderList(array $condition = array())
    {
        //TODO
    }

    /**
     * 获取订单总数
     * @param array $condition
     * @return int
     */
    public function getOrderCount(array $condition = array())
    {

    }

    /**
     * 创建订单
     */
    public function createOrder()
    {

    }

    /**
     * 编辑订单
     */
    public function editOrder()
    {

    }

    /**
     * 删除订单
     */
    public function deleteOrder()
    {

    }

    /**
     * 初始化订单钩子
     */
    public function initHooks()
    {

    }

    /**
     * 绑定订单钩子
     */
    public function bindHooks()
    {

    }

    /**
     * 触发订单事件
     */
    public function trigger()
    {

    }
}