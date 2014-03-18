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
namespace Lychee;

use Lychee\Utils\Encrypt as Encrypt;

/**
 * 荔枝类库配置类
 * @package Lychee
 * @author Samding
 */
class Config
{

    /**
     * 标记配置是否已加载
     * @var bool
     */
    private static $is_load = false;

    /**
     * 外部模块列表
     * @var array
     */
    private static $external_module = array();

    /**
     * 内部模块列表
     * @var array
     */
    private static $internal_module = array();

    /**
     * 配置信息
     * @var array
     */
    private static $data = array();

    private function __construct()
    {

    }

    /**
     * 手动注册外部模块
     * @param string $module_dir
     */
    public static function register($module_dir)
    {
        if (file_exists($module_dir)) {
            $module_name = pathinfo($module_dir, PATHINFO_BASENAME);
            $module_name = strtolower($module_name);
            self::$external_module[$module_name] = $module_dir;
        }
    }

    /**
     * 读取运行时
     * @param string $name
     * @return array
     */
    private static function readRuntime($name)
    {
        $runtime_file = LYCHEE_RUNTIME . DIRECTORY_SEPARATOR . "{$name}.runtime";
        $content = file_get_contents($runtime_file);
        $temp = self::decrypt($content, __CLASS__);
        $data = unserialize($temp);
        return $data;
    }

    /**
     * 写入运行时
     * @param array $data
     * @param string $name
     */
    private static function writeRuntime(array $data, $name)
    {
        $runtime_file = LYCHEE_RUNTIME . DIRECTORY_SEPARATOR . "{$name}.runtime";
        $temp = serialize($data);
        $content = self::encrypt($temp, __CLASS__);
        $handle = fopen($runtime_file, 'w');
        flock($handle, LOCK_EX);
        fputs($handle, $content);
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    /**
     * 判断运行时是否存在
     * @param $name
     * @return bool
     */
    private static function isRuntimeExist($name)
    {
        $runtime_file = LYCHEE_RUNTIME . DIRECTORY_SEPARATOR . "{$name}.runtime";
        return file_exists($runtime_file);
    }

    /**
     * 自动注册内部模块
     */
    private static function autoRegister()
    {
        if (!LYCHEE_DEBUG && self::isRuntimeExist('internal_module')) {
            self::$internal_module = self::readRuntime('internal_module');
            if (!empty(self::$internal_module)) {
                return;
            }
        }
        //扫描并注册内部模块
        $root_path = LYCHEE_ROOT;//搜索路径
        $handle = opendir($root_path);
        $ignore_dir = array('.', '..', 'runtime');
        while ($file = readdir($handle)) {
            if (in_array($file, $ignore_dir)) {
                continue;
            }
            $path = $root_path . DIRECTORY_SEPARATOR . $file;
            //只扫描文件夹
            if (!is_dir($path)) {
                continue;
            }
            $module_name = strtolower($file);
            self::$internal_module[$module_name] = $path;
        }
        closedir($handle);
        if (!LYCHEE_DEBUG) {
            self::writeRuntime(self::$internal_module, 'internal_module');
        }
    }

    /**
     * 初始化配置
     * @param array $custom_config
     */
    public static function init(array $custom_config)
    {
        //已经加载过配置
        if (self::$is_load) {
            return;
        }
        if (!LYCHEE_DEBUG && self::isRuntimeExist('lychee_config')) {
            self::$data = self::readRuntime('lychee_config');
            if (!empty(self::$data)) {
                self::$is_load = true;
                return;
            }
        }
        self::autoRegister();//自动注册内部模块
        $avaliable_module = array_merge(self::$internal_module, self::$external_module);//当前有效模块
        $convention_config = array();
        //读取模块默认配置
        foreach ($avaliable_module as $module_name => $module_path) {
            $config_path = $module_path . DIRECTORY_SEPARATOR . 'convention.php';
            if (file_exists($config_path)) {
                $convention_config[$module_name] = include $config_path;
            }
            else {
                $convention_config[$module_name] = array();
            }
        }

        //检查传入的配置
        if (!isset($custom_config['base'])) {
            $temp = array();
            $temp['base'] = $custom_config;
            $custom_config = $temp;
        }
        //合并配置
        $lychee_config = self::mergeConfig($convention_config, $custom_config);
        $base_convention = $lychee_config['base'];
        $output = array();
        $callback = function ($value, $key) use (&$output, $base_convention)
        {
            $output[$key] = array_merge($base_convention, $value);
        };
        array_walk($lychee_config, $callback);
        self::$data = $output;
        self::$is_load = true;
        if (!LYCHEE_DEBUG) {
            self::writeRuntime(self::$data, 'lychee_config');
        }
    }

    /**
     * 合并配置内部方法
     * @param mixed $old
     * @param mixed $new
     * @return array
     */
    private static function __mergeConfig($old, $new)
    {
        foreach ($old as $key => $value) {
            if (is_array($value)) {
                if (isset($new[$key]))
                    $new[$key] = self::__mergeConfig($value, $new[$key]);
                else
                    $new[$key] = $value;
            }
            else {
                if (!isset($new[$key])) {
                    $new[$key] = $value;
                }
            }
        }
        return $new;
    }

    /**
     * 合并配置
     * @param array $old
     * @param array $new
     * @return array
     */
    private static function mergeConfig(array $old, array $new)
    {
        $result = array_merge($old, $new);
        $result = self::__mergeConfig($old, $result);
        return $result;
    }

    /**
     * 获取配置值
     * @throws \Exception
     * @param string $name 配置项
     * @param mixed $default 无此项目时返回该参数，默认为null
     * @return mixed
     */
    public static function get($name, $default = null)
    {
        if (!self::$is_load) {
            throw new \Exception("pls call " . __CLASS__ . "::load first.");
        }
        $path = explode('.', $name);
        $len = count($path);
        $result = null;
        for ($i = 0; $i < $len; $i++) {
            $key = $path[$i];
            $key = strtolower($key);
            if ($i == 0) {
                $result = isset(self::$data[$key])?self::$data[$key]:null;
            }
            else {
                $result = isset($result[$key])?$result[$key]:null;
            }
            if (is_null($result)) {
                $result = $default;
                break;
            }
        }
        return $result;
    }

    /**
     * 加密
     * @param string $content
     * @return string
     */
    private static function encrypt($content)
    {
        return Encrypt::encrypt($content);
    }

    /**
     * 解密
     * @param string $content
     * @return string
     */
    private static function decrypt($content)
    {
        return Encrypt::decrypt($content);
    }
}