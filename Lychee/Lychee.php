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
 * 荔枝类库入口脚本
 * 执行必须初始化工作
 * @author Samding
 */
defined('LYCHEE_ROOT') || define('LYCHEE_ROOT', dirname(__FILE__));
defined('LYCHEE_DEBUG') || define('LYCHEE_DEBUG', true);
defined('LYCHEE_RUNTIME') || define('LYCHEE_RUNTIME', LYCHEE_ROOT . DIRECTORY_SEPARATOR . 'runtime');
include LYCHEE_ROOT . DIRECTORY_SEPARATOR . 'Loader.php';

//初始化自动加载
Loader::register();

/**
 * 初始化
 * 传入配置数组快速初始化荔枝
 * @param array $config
 */
function init(array $config = array())
{
    Config::init($config);
}

/**
 * 注册外部模块
 * @param string $module_dir
 */
function register($module_dir)
{
    Config::register($module_dir);
}

/**
 * 清除*.runtime文件
 */
function erase_runtime()
{
    $handle = opendir(LYCHEE_RUNTIME);
    $skip = array('.', '..');
    while ($file = readdir($handle)) {
        if (in_array($file, $skip)) {
            continue;
        }
        $file_path = LYCHEE_RUNTIME . DIRECTORY_SEPARATOR . $file;
        @unlink($file_path);
    }
    closedir($handle);
}