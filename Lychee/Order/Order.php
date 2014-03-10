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
     * 事件钩子
     * @var array
     */
    private $hooks = array();

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
        $order_id = intval($order_id);
        if ($order_id < 1) {
            return array();
        }
        $order_info = $this->order->where(array('order_id' => $order_id))->select(true);
        return $order_info;
    }

    /**
     * 获取订单列表
     * @param array $condition
     * @return array
     */
    public function getOrderList(array $condition = array())
    {
        return $this->order->where($condition)->select();
    }

    /**
     * 获取订单总数
     * @param array $condition
     * @return int
     */
    public function getOrderCount(array $condition = array())
    {
        return $this->order->where($condition)->count();
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
     * @param int $order_id
     * @return int
     */
    public function cancelOrder($order_id)
    {
        $order_id = intval($order_id);
        if ($order_id < 1) {
            return 0;
        }
        return $this->order->where(array('order_id' => $order_id))->data(array('status' => -1))->update();
    }

    /**
     * 初始化订单钩子
     * @param $hooks
     */
    public function initHooks(array $hooks)
    {
        if (!empty($this->hooks)) {
            $this->hooks = $hooks;
        }
    }

    /**
     * 添加订单钩子
     * @param int $type
     * @param string $class
     */
    public function addHook($type, $class)
    {
        $this->hooks[$type] = $class;
    }

    /**
     * 添加订单钩子设置
     * @param array $hooks
     */
    public function appendHooks(array $hooks)
    {
        foreach ($hooks as $type => $class) {
            $this->addHook($type, $class);
        }
    }

    /**
     * 触发订单事件
     * @param int $order_id
     * @param string $type
     * @return bool
     */
    public function trigger($order_id, $type)
    {
        $order_id = intval($order_id);
        if ($order_id < 1) {
            return false;
        }
        $order_info = $this->order->where(array('order_id' => $order_id))->field('type_id')->select(true);
        if (empty($order_info)) {
            return false;
        }
        $type_id = $order_info['type_id'];
        if (!isset($this->hooks[$type_id])) {
            //没有该订单类型的事件处理器绑定
            return true;
        }
        $class_name = $this->hooks[$type_id];
        $class = new \ReflectionClass($class_name);
        $instance = $class->newInstance();
        if (!$instance instanceof InterfaceHandler) {
            return false; //不正确的绑定类
        }
        //根据事件类型获取方法名
        $type = trim($type);
        $type = str_replace('_', ' ', $type);
        $type = ucwords($type);
        $method_name = 'on' . str_replace(' ', '_', $type);
        $method = new \ReflectionMethod($class_name, $method_name);
        return $method->invokeArgs($instance, array($order_id));
    }
}