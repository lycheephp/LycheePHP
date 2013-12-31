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
 * 文件缓存类
 * @author Samding
 * @package Lychee\Base\Cache
 */
class File
{
    /**
     * 缓存目录
     * @var string
     */
    private $cache_dir;

    /**
     * 构造器
     * @param string $cache_dir
     */
    public function __construct($cache_dir)
    {
        if (!file_exists($cache_dir)) {
            mkdir($cache_dir, 0777, true);
        }
        if (!is_dir($cache_dir))
        {
            $cache_dir = dirname($cache_dir);
        }
        $this->cache_dir = $cache_dir;
    }

    /**
     * 根据缓存名定位缓存文件路径
     * @param string $name
     * @return string
     */
    private function getFilePath($name)
    {
        $file_name = md5($name);
        $sub_dir = substr($file_name, 0, 2);
        $cache_dir = "{$this->cache_dir}/{$sub_dir}";
        if (!file_exists($cache_dir)) {
            mkdir($cache_dir, 0777, true);
        }
        return $cache_dir . DIRECTORY_SEPARATOR . $file_name;
    }

    /**
     * 魔术方法
     * 获取缓存
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
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
        $this->set($name, $value, 0);
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
     * @return int
     */
    public function decrement($name, $offset = 1)
    {
        return $this->increment($name, $offset * -1);
    }

    /**
     * 删除缓存
     * @param string $name
     */
    public function del($name)
    {
        $key = trim($name);
        $file_path = $this->getFilePath($key);
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    /**
     * 删除多个缓存
     * @param array $keys
     */
    public function delMulti(array $keys)
    {
        foreach ($keys as $value) {
            $this->del($value);
        }
    }

    /**
     * 获取缓存
     * @param string $name
     * @param mixed $default 不存在时返回该值
     * @return mixed
     */
    public function get($name, $default = false)
    {
        $key = trim($name);
        $file_path = $this->getFilePath($key);
        //读取
        if(!file_exists($file_path)) {
            return $default;
        }
        $handle = fopen($file_path, 'r');
        if (!$handle) {
            return $default;
        }
        flock($handle, LOCK_SH);
        $serial = fgets($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
        $cache_content = unserialize($serial);
        if ($cache_content === false) {
            return $default;
        }
        $add_time = isset($cache_content['add_time'])?$cache_content['add_time']:0;
        $life_time = isset($cache_content['life_time'])?$cache_content['life_time']:1;
        if (time() > $life_time + $add_time && $life_time != 0) {
            //缓存过期
            //unlink($file_path);
            return $default;
        }
        return isset($cache_content['data'])?$cache_content['data']:$default;
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
     * @return int
     */
    public function increment($name, $offset = 1)
    {
        $key = trim($name);
        $value = intval($offset);
        $file_path = $this->getFilePath($key);
        if (!file_exists($file_path)) {
            //不存在则创建
            $this->set($key, 0, 0);
        }
        //读取缓存
        $handle = fopen($file_path, 'r+');
        if (!$handle) {
            //文件IO错误
            return 0;
        }
        flock($handle, LOCK_EX);
        $serial = fgets($handle);
        $cache_content = unserialize($serial);
        $data = $cache_content['data'];
        if (!is_numeric($data)) {
            //元素值不是数字
            //重设为0
            $data = 0;
        }
        $data += $value;
        $cache_content['life_time'] = 0;
        $cache_content['data'] = $data;
        //清空并重写缓存文件内容
        $serial = serialize($cache_content);
        rewind($handle);//重置文件指针位置
        fputs($handle, '', filesize($file_path));
        fputs($handle, $serial);
        flock($handle, LOCK_UN);
        fclose($handle);

        return $data;
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
        $key = trim($name);
        $file_path = $this->getFilePath($key);
        $life_time = intval($life_time);
        $cache_content = array();
        $cache_content['add_time'] = time();
        $cache_content['life_time'] = $life_time;
        $cache_content['data'] = $value;
        $serial = serialize($cache_content);
        //写入
        $result = false;
        $handle = fopen($file_path, 'w');
        if ($handle) {
            flock($handle, LOCK_EX);
            fputs($handle, $serial);
            flock($handle, LOCK_UN);
            fclose($handle);
            $result = true;
        }
        return $result;
    }

    /**
     * 设置多个缓存
     * @param array $items
     * @param int $life_time
     */
    public function setMulti(array $items, $life_time=3600)
    {
        $callback = function($value, $key) use ($life_time)
        {
            $this->set($key, $value, $life_time);
        };
        array_walk($items, $callback);
    }
}