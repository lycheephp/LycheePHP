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

use Lychee;

/**
 * 缓存工厂类
 * @author Samding
 * @package Lychee\Base\Cache
 */
class Factory
{

    /**
     * 构造器
     * 不要实例化该类
     */
    private function __construct()
    {

    }

    /**
     * 创建缓存实例
     * @return mixed
     * @throws \Exception
     */
    public static function create()
    {
        $cache_type = Lychee\Config::get('base.cache.type', 'file');
        $class_name = __NAMESPACE__ + '/' + ucwords($cache_type);
        if (!class_exists($class_name)) {
            throw new \Exception('Not supported cache type');
        }
        $class = new \ReflectionClass($class_name);
        $method = $class->getMethod('getInstance');
        return $method->invoke(null);
    }
}