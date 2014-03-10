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

/**
 * 订单钩子处理器接口
 * @author Samding
 * @package Lychee\Order
 */
interface InterfaceHandler
{

    /**
     * 订单创建前触发
     * @return bool
     */
    public function onBeforeCreate();

    /**
     * 订单创建后触发
     * @param int $order_id
     * @return bool
     */
    public function onAfterCreate($order_id);

    /**
     * 订单确认前触发
     * @param int $order_id
     * @return bool
     */
    public function onBeforeConfirm($order_id);

    /**
     * 订单确认后触发
     * @param int $order_id
     * @return bool
     */
    public function onAfterConfirm($order_id);

    /**
     * 订单支付前触发
     * @param int $order_id
     * @return bool
     */
    public function onBeforePay($order_id);

    /**
     * 订单支付后出发
     * @param int $order_id
     * @return bool
     */
    public function onAfterPay($order_id);

    /**
     * 订单完成前触发
     * @param int $order_id
     * @return bool
     */
    public function onBeforeComplete($order_id);

    /**
     * 订单完成后触发
     * @param int $order_id
     * @return bool
     */
    public function onAfterComplete($order_id);

}