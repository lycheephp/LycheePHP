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

use Lychee\Base\Logger as Logger;
use Lychee\Base\MySQL\Driver as MySQLDriver;

/**
 * 荔枝服务类
 * @author Samding
 * @package Lychee
 */
class Service
{

    /**
     * 服务容器
     * @var array
     */
    private static $container = array();

    /**
     * 构造器
     */
    private function __construct()
    {

    }

    /**
     * 获取服务
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function get($name, $default = null)
    {
        $name = trim($name);
        $name = strtolower($name);
        return isset(self::$container[$name])?self::$container[$name]:$default;
    }

    /**
     * 执行服务初始化
     */
    public static function initialize()
    {
        self::initLogger();
        self::initMySQL();
    }

    /**
     * 初始化日志服务
     */
    private static function initLogger()
    {
        $log_dir = Config::get('base.logger.log_dir');
        self::$container['logger'] = new Logger($log_dir);
    }

    /**
     * 初始化MySQL数据库连接服务
     */
    private static function initMySQL()
    {
        $host = Config::get('base.mysql.host');
        $port = Config::get('base.mysql.port');
        $username = Config::get('base.mysql.username');
        $password = Config::get('base.mysql.password');
        $charset = Config::get('base.mysql.charset');
        self::$container['mysql'] = new MySQLDriver($host, $port, $username, $password, $charset);
    }
}

Service::initialize();