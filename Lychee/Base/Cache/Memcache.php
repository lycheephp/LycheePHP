<?php
/**
 * Copyright 2013 henryzengpn(Henryzeng) koboshi(Samding)
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
namespace Lychee\Base\Cache;

/**
 * Memcache缓存类
 * @author Samding
 * @package Lychee\Base\Cache
 */
class Memcache
{
    /**
     * memcache连接对象
     * @var \Memcache
     */
    private $memcache;

    /**
     * 主机地址
     * @var string
     */
    private $host;

    /**
     * 服务器端口
     * @var int
     */
    private $port;

    /**
     * 实例
     * @var Memcache
     */
    private static $instance = null;

    /**
     * 单实例
     * @param string $host
     * @param int $port
     */
    private function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * 获取实例
     * @param array $config
     * @return Memcache
     */
    public static function getInstance(array $config = array())
    {
        if (!empty($config)) {
            $host = isset($config['host'])?$config['host']:'localhost';
            $port = isset($config['port'])?$config['port']:11211;
            self::$instance = new Memcache($host, $port);
        }
        return self::$instance;
    }

    /**
     * 发起连接
     */
    private function connect()
    {
        $host = $this->host;
        $port = $this->port;
        if (is_null($this->memcache)) {
            $this->memcache = new \Memcache();
            $this->memcache->addServer($host, $port);
        }
    }

    /**
     * 魔术方法
     * 获取缓存
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name, false);
    }

    /**
     * 魔术方法
     * 检查缓存是否存在
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->get($name, false) !== false;
    }

    /**
     * 魔术方法
     * 设置缓存
     * 默认一小时生存期
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        $this->set($name, $value, 3600);
    }

    /**
     * 魔术方法
     * 删除缓存
     * @param string $name
     */
    public function __unset($name)
    {
        $this->del($name);
    }

    /**
     * 递减一个缓存元素的值
     * 该元素值不会过期，初始值为0
     * 返回递减后的值
     * 无并发
     * @param string $name
     * @param int $offset
     * @return int|bool
     */
    public function decrement($name, $offset = 1)
    {
        $this->connect();
        $key = trim($name);
        $key = md5(strtolower($key));
        $flag = $this->memcache->decrement($key, $offset);
        return $flag;
    }

    /**
     * 删除缓存
     * @param string $name
     * @return bool
     */
    public function del($name)
    {
        $this->connect();
        $key = trim($name);
        $key = md5(strtolower($key));
        return $this->memcache->delete($key);
    }

    /**
     * 删除多个缓存
     * @param array $keys
     * @return bool
     */
    public function delMulti(array $keys)
    {
        $flag = true;
        foreach ($keys as $value) {
            $flag = $this->del($value) && $flag;
        }
        return $flag;
    }

    /**
     * 获取缓存
     * @param string $name
     * @param mixed $default 不存在时返回该值
     * @return mixed
     */
    public function get($name, $default = false)
    {
        $this->connect();
        $key = trim($name);
        $key = md5(strtolower($key));
        $data = @$this->memcache->get($key);
        if ($data === false) {
            return $default;
        }
        return $data;
    }

    /**
     * 获取多个缓存
     * 任意一个缓存不存在时返回default值
     * @param array $keys
     * @param mixed $default
     * @return mixed
     */
    public function getMulti(array $keys, $default = array())
    {
        $output = array();
        foreach ($keys as $key) {
            $temp = $this->get($key, false);
            if ($temp === false) {
                return $default;
            }
            $output[$key] = $temp;
        }
        return empty($output)?$default:$output;
    }

    /**
     * 递增一个缓存元素的值
     * 该元素值不会过期,初始值为0
     * 返回递增后的值
     * 无并发
     * @param string $name
     * @param int $offset
     * @return int|bool
     */
    public function increment($name, $offset = 1)
    {
        $this->connect();
        $key = trim($name);
        $key = md5(strtolower($key));
        $flag = $this->memcache->increment($key, $offset);
        return $flag;
    }

    /**
     * 设置缓存
     * @param string $name
     * @param mixed $value
     * @param int $life_time 缓存有效期，单位（秒）
     * @return bool
     */
    public function set($name, $value, $life_time = 3600)
    {
        $this->connect();
        $key = trim($name);
        $key = md5(strtolower($key));
        return $this->memcache->set($key, $value, null, $life_time);
    }

    /**
     * 设置多个缓存
     * @param array $items
     * @param int $life_time
     * @return bool
     */
    public function setMulti(array $items, $life_time=3600)
    {
        $flag = true;
        $callback = function($value, $key) use ($life_time, &$flag)
        {
            $flag = $flag && $this->set($key, $value, $life_time);
        };
        array_walk($items, $callback);
        return $flag;
    }
}