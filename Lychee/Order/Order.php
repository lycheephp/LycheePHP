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
 * 订单模块订单逻辑类
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
     * 状态:已取消
     */
    const STATUS_CANCELED = -1;

    /**
     * 状态:已创建
     */
    const STATUS_CREATED = 0;

    /**
     * 状态:已确认
     */
    const STATUS_CONFIRMED = 1;

    /**
     * 状态:已支付
     */
    const STATUS_PAID = 2;

    /**
     * 状态:已完成
     */
    const STATUS_COMPLETED = 3;

    /**
     * 错误:商品不存在
     */
    const ERR_GOODS_NOT_EXIST = -1;

    /**
     * 错误:商品库存不足
     */
    const ERR_OUT_OF_STOCK = -2;

    /**
     * 错误:订单不存在
     */
    const ERR_ORDER_NOT_EXIST = -3;

    /**
     * 错误:订单状态不正确
     */
    const ERR_ORDER_STATUS = -4;

    /**
     * 错误:内部
     */
    const ERR_INTERNAL = -99;

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
    private $order_detail;

    /**
     * 订单日志表查询类
     * @var QueryHelper
     */
    private $order_log;

    /**
     * 构造器
     */
    public function __construct()
    {
        $db_name = Config::get('order.mysql.db_name');
        $this->order = new QueryHelper('order', $db_name);
        $this->order_detail = new QueryHelper('order_detail', $db_name);
        $this->order_log = new QueryHelper('order_log', $db_name);
    }

    /**
     * 添加订单日志
     * @param array $data
     * @return int
     */
    public function addLog(array $data)
    {
        if (empty($data)) {
            return 0;
        }
        return $this->order_log->data($data)->insert();
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
     * 获取订单的产品信息列表
     * @param int $order_id
     * @return array
     */
    public function getOrderDetailList($order_id)
    {
        $order_id = intval($order_id);
        if ($order_id < 1) {
            return array();
        }
        $order_info = $this->order_detail->where(array('order_id' => $order_id))->select();
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
     * 生成订单号码
     * @return string
     */
    private static function generateOrderNo()
    {
        $micro = microtime(true);
        $rand = ($micro - intval($micro)) * 1000;
        $rand = intval($rand);
        return date('YmdHis') . $rand . uniqid();
    }

    /**
     * 创建订单
     * @param array $order_info 订单信息
     * @param array $goods_info 商品信息 array('goods_id' => 'num', ...)
     * @return int
     */
    public function createOrder(array $order_info, array $goods_info)
    {
        if (empty($order_info) || empty($goods_info)) {
            return self::ERR_INTERNAL;
        }
        //整理订单信息
        $order_no = isset($order_info['order_no'])?$order_info['order_no']:self::generateOrderNo();
        $type_id = isset($order_info['type_id'])?intval($order_info['type_id']):0;
        $user_id = isset($order_info['user_id'])?intval($order_info['user_id']):0;
        $zip = isset($order_info['zip'])?trim($order_info['zip']):'';
        $mobile = isset($order_info['mobile'])?trim($order_info['mobile']):'';
        $city_id = isset($order_info['city_id'])?intval($order_info['city_id']):0;
        $address = isset($order_info['address'])?intval($order_info['address']):'';
        $cost_price = isset($order_info['cost_price'])?floatval($order_info['cost_price']):0.0;
        $total_price = isset($order_info['total_price'])?floatval($order_info['total_price']):0.0;
        $strike_price = isset($order_info['strike_price'])?floatval($order_info['strike_price']):0.0;
        $shipping_price = isset($order_info['shipping_price'])?floatval($order_info['shipping_price']):0.0;
        $add_time = time();
        $update_time = $add_time;

        //开始事务
        $this->order->begin();

        //整理订单商品信息
        $order_goods_list = array();
        $goods = new Goods();
        foreach ($goods_info as $value) {
            if (empty($value) || !isset($value['goods_id']) || !isset($value['num'])) {
                return self::ERR_INTERNAL;//传入的商品信息不正确
            }
            $goods_id = intval($value['goods_id']);
            $num = intval($value['num']);
            if ($num < 1) {
                continue;//购买0件，跳过
            }
            //获取商品信息
            $goods_info = $goods->getGoodsInfo($goods_id);
            if (empty($goods_info) || $goods_info['status'] == 0) {
                $this->order->rollback();
                return self::ERR_GOODS_NOT_EXIST;//商品不存在
            }
            $flag = 1;
            if (!$goods_info['unlimited_stock']) {
                $flag = $goods->decreaseStock($goods_id, $num);//尝试减少库存
            }
            if (!$flag) {
                $this->order->rollback();
                return self::ERR_OUT_OF_STOCK;//购买数大于商品剩余库存数
            }
            $data = array();
            $data['goods_id'] = $goods_id;
            $data['num'] = $num;
            $data['cost_price'] = $goods_info['cost_price'];
            $data['net_price'] = $goods_info['net_price'];
            $data['price'] = $goods_info['price'];
            $data['strike_price'] = $goods_info['price'];
            $order_goods_list[] = $data;
        }

        if (empty($order_goods_list)) {
            $this->order->rollback();
            return self::ERR_GOODS_NOT_EXIST;//没有要购买的商品
        }

        //整理价格
        if (empty($cost_price)) {
            foreach ($order_goods_list as $row) {
                $cost_price += $row['cost_price'] * $row['num'];
            }
        }
        if (empty($strike_price)) {
            foreach ($order_goods_list as $row) {
                $strike_price += $row['strike_price'] * $row['num'];
            }
        }
        if (empty($total_price)) {
            $total_price = $strike_price + $shipping_price;//订单总价=商品成交价格+运费
        }

        //创建订单
        $data = array();
        $data['order_no'] = $order_no;
        $data['type_id'] = $type_id;
        $data['user_id'] = $user_id;
        $data['zip'] = $zip;
        $data['mobile'] = $mobile;
        $data['city_id'] = $city_id;
        $data['address'] = $address;
        $data['cost_price'] = $cost_price;
        $data['total_price'] = $total_price;
        $data['strike_price'] = $strike_price;
        $data['shipping_price'] = $shipping_price;
        $data['add_time'] = $add_time;
        $data['update_time'] = $update_time;
        $data['status'] = self::STATUS_CREATED;
        $order_id = $this->order->data($data)->insert();
        if ($order_id < 1) {
            $this->order->rollback();
            return self::ERR_INTERNAL;//订单创建失败
        }
        foreach ($order_goods_list as $key => $row) {
            $order_goods_list[$key]['order_id'] = $order_id;
        }
        foreach ($order_goods_list as $row) {
            $this->order_detail->data($row)->insert();
        }
        $flag = $this->trigger($order_id, self::AFTER_CREATE);//触发事件
        if (!$flag) {
            $this->order->rollback();
            return self::ERR_INTERNAL;
        }
        $this->order->commit();
        return $order_id;
    }

    /**
     * 确认订单
     * @param int $order_id
     * @param array $order_info
     * @return int
     */
    public function confirmOrder($order_id, array $order_info = array())
    {
        $order_id = intval($order_id);
        if ($order_id < 1) {
            return self::ERR_ORDER_NOT_EXIST;
        }
        $this->order->begin();//事务开始
        $result = $this->order->where(array('order_id' => $order_id))->field('status')->select(true);
        if (empty($result)) {
            return self::ERR_ORDER_NOT_EXIST;//不存在该订单;
        }
        if ($result['status'] != self::STATUS_CREATED) {
            return self::ERR_ORDER_STATUS;//订单不能被确认
        }
        $flag = $this->trigger($order_id, self::BEFORE_CONFIRM);//触发事件
        if (!$flag) {
            return self::ERR_INTERNAL;
        }
        $order_info['status'] = self::STATUS_CONFIRMED;//订单确认
        $order_info['update_time'] = time();
        unset($order_info['order_id']);
        unset($order_info['order_no']);
        $this->order->where(array('order_id' => $order_id))->data($order_info)->update();
        $flag = $this->trigger($order_id, self::AFTER_CONFIRM);//触发事件
        if (!$flag) {
            $this->order->rollback();
            return self::ERR_INTERNAL;
        }
        $this->order->commit();
        $data = array();
        $data['order_id'] = $order_id;
        $data['source_status'] = self::STATUS_CREATED;
        $data['target_status'] = self::STATUS_CONFIRMED;
        $data['add_time'] = time();
        $this->addLog($data);
        return $order_id;
    }

    /**
     * 支付订单
     * @param int $order_id
     * @return int
     */
    public function payOrder($order_id)
    {
        $order_id = intval($order_id);
        if ($order_id < 1) {
            return self::ERR_ORDER_NOT_EXIST;
        }
        $this->order->begin();
        $result = $this->order->where(array('order_id' => $order_id))->field('status')->select(true);
        if (empty($result)) {
            return self::ERR_ORDER_NOT_EXIST;//不存在该订单;
        }
        if ($result['status'] != self::STATUS_CONFIRMED) {
            return self::ERR_ORDER_STATUS;//订单不能被支付
        }
        $flag = $this->trigger($order_id, self::BEFORE_PAY);//触发事件
        if (!$flag) {
            return self::ERR_INTERNAL;
        }
        $this->order->where(array('order_id' => $order_id))->data(array('status' => self::STATUS_PAID, 'update_time' => time()))->update();
        $flag = $this->trigger($order_id, self::AFTER_PAY);//触发事件
        if (!$flag) {
            $this->order->rollback();
            return self::ERR_INTERNAL;
        }
        $this->order->commit();
        $data = array();
        $data['order_id'] = $order_id;
        $data['source_status'] = self::STATUS_CONFIRMED;
        $data['target_status'] = self::STATUS_PAID;
        $data['add_time'] = time();
        $this->addLog($data);
        return $order_id;
    }

    /**
     * 完成订单
     * @param int $order_id
     * @return int
     */
    public function completeOrder($order_id)
    {
        $order_id = intval($order_id);
        if ($order_id < 1) {
            return self::ERR_ORDER_NOT_EXIST;
        }
        $this->order->begin();
        $result = $this->order->where(array('order_id' => $order_id))->field('status')->select(true);
        if (empty($result)) {
            return self::ERR_ORDER_NOT_EXIST;//不存在该订单;
        }
        if ($result['status'] != self::STATUS_PAID) {
            return self::ERR_ORDER_STATUS;//订单不能被完成
        }
        $flag = $this->trigger($order_id, self::BEFORE_COMPLETE);//触发事件
        if (!$flag) {
            return self::ERR_INTERNAL;
        }
        $this->order->where(array('order_id' => $order_id))->data(array('status' => self::STATUS_COMPLETED, 'update_time' => time()))->update();
        $flag = $this->trigger($order_id, self::AFTER_COMPLETE);//触发事件
        if (!$flag) {
            $this->order->rollback();
            return self::ERR_INTERNAL;
        }
        $this->order->commit();
        $data = array();
        $data['order_id'] = $order_id;
        $data['source_status'] = self::STATUS_PAID;
        $data['target_status'] = self::STATUS_COMPLETED;
        $data['add_time'] = time();
        $this->addLog($data);
        return $order_id;
    }


    /**
     * 编辑订单
     * @param array $data
     * @param int $order_id
     * @return int
     */
    public function editOrder(array $data, $order_id)
    {
        $order_id = intval($order_id);
        if ($order_id < 1) {
            return self::ERR_ORDER_NOT_EXIST;
        }
        //不出不能编辑的字段
        unset($data['order_id']);
        unset($data['order_no']);
        unset($data['type_id']);
        unset($data['status']);
        $data['update_time'] = time();
        return $this->order->where(array('order_id' => $order_id))->data($data)->update();
    }

    /**
     * 恢复删除的订单
     * @param int $order_id
     * @return int
     */
    public function recoverOrder($order_id)
    {
        $order_id = intval($order_id);
        if ($order_id < 1) {
            return self::ERR_ORDER_NOT_EXIST;
        }
        $this->order->begin();
        $order_info = $this->getOrderInfo($order_id);
        if (empty($order_info)) {
            return self::ERR_ORDER_NOT_EXIST;//订单不存在
        }
        $order_status = $order_info['status'];
        if ($order_status != self::STATUS_CANCELED) {
            return self::ERR_ORDER_STATUS;//订单不能恢复
        }
        $this->order->where(array('order_id' => $order_id))->data(array('status' => self::STATUS_CREATED, 'update_time' => time()))->update();
        //重新扣除
        $order_goods_list = $this->order_detail->where(array('order_id' => $order_id))->select();
        $goods = new Goods();
        foreach ($order_goods_list as $row) {
            $goods_id = $row['goods_id'];
            $num = $row['num'];
            //获取商品信息
            $goods_info = $goods->getGoodsInfo($goods_id);
            if (empty($goods_info) || $goods_info['status'] == 0) {
                $this->order->rollback();
                return self::ERR_GOODS_NOT_EXIST;//商品不存在
            }
            $flag = 1;
            if (!$goods_info['unlimited_stock']) {
                $flag = $goods->decreaseStock($goods_id, $num);//尝试减少库存
            }
            if (!$flag) {
                $this->order->rollback();
                return self::ERR_OUT_OF_STOCK;//购买数大于商品剩余库存数
            }
        }
        $this->order->commit();
        $data = array();
        $data['order_id'] = $order_id;
        $data['source_status'] = self::STATUS_CANCELED;
        $data['target_status'] = self::STATUS_CREATED;
        $data['add_time'] = time();
        $this->addLog($data);
        return 1;
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
            return self::ERR_ORDER_NOT_EXIST;
        }
        $this->order->begin();
        //检查订单状态
        $order_info = $this->getOrderInfo($order_id);
        if (empty($order_info)) {
            return self::ERR_ORDER_NOT_EXIST;//订单不存在
        }
        $order_status = $order_info['status'];
        if (!($order_status == self::STATUS_CANCELED || $order_status == self::STATUS_CREATED || $order_status == self::STATUS_CREATED)) {
            return self::ERR_ORDER_STATUS;//订单不能取消
        }
        $flag = $this->order->where(array('order_id' => $order_id))->data(array('status' => self::STATUS_CANCELED, 'update_time' => time()))->update();
        //还原库存
        $order_goods_list = $this->order_detail->where(array('order_id' => $order_id))->select();
        $goods = new Goods();
        foreach ($order_goods_list as $row) {
            $goods_id = $row['goods_id'];
            $num = $row['num'];
            $goods->increaseStock($goods_id, $num);
        }
        $this->order->commit();
        $data = array();
        $data['order_id'] = $order_id;
        $data['source_status'] = $order_info['status'];
        $data['target_status'] = self::STATUS_CANCELED;
        $data['add_time'] = time();
        $this->addLog($data);
        return $flag;
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
        if (empty($this->hooks)) {
            //没有任何事件处理器绑定
            return true;
        }
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