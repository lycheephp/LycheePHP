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
     * 配置信息
     * @var array
     */
    private static $data = array();

    private function __construct()
    {

    }

    /**
     * 加载配置信息
     * @param array $config
     */
    public static function load(array $config)
    {
        //已经加载过配置则退出
        if (self::$is_load) {
            return;
        }
        //查看当前组件
        $convention_config = array();
        $root_path = LYCHEE_ROOT;//搜索路径
        $handle = opendir($root_path);
        $ignore_dir = array('.', '..');
        while ($file = readdir($handle)) {
            if (!in_array($file, $ignore_dir)) {
                $path = $root_path . DIRECTORY_SEPARATOR . $file;
                //只扫描文件夹
                if (!is_dir($path)) {
                    continue;
                }
                //读取该组件的默认配置
                $config_path = $path . DIRECTORY_SEPARATOR . 'convention.php';
                $key = strtolower($file);
                if (file_exists($config_path)) {
                    $convention_config[$key] = include $config_path;
                }
                else {
                    $convention_config[$key] = array();
                }
            }
        }
        closedir($handle);

        //检查传入的配置
        if (!isset($config['base'])) {
            $temp = array();
            $temp['base'] = $config;
            $config = $temp;
        }
        //合并配置
        $lychee_config = array_merge($convention_config, $config);
        $base_convention = $lychee_config['base'];
        $output = array();
        $callback = function ($value, $key) use (&$output, $base_convention)
        {
            $output[$key] = array_merge($base_convention, $value);
        };
        array_walk($lychee_config, $callback);
        self::$data = $output;
        self::$is_load = true;
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
}