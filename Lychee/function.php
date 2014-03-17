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
 * 初始化
 * 传入配置数组快速初始化荔枝
 * @param array $config
 */
function init(array $config = array())
{
    Config::init($config);
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

/**
 * 注册外部模块
 * @param string $module_dir
 */
function register($module_dir)
{
    Config::register($module_dir);
}