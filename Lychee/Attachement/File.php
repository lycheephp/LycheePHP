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
namespace Lychee\Attachment;

use Lychee\Config as Config;
use Lychee\Base\MySQL\QueryHelper as QueryHelper;

/**
 * 附件模块文件逻辑类
 * @author Samding
 * @package Lychee\Attachment
 */
class File
{

    /**
     * 文件表查询类
     * @var QueryHelper
     */
    private $file;

    /**
     * 构造器
     */
    public function __construct()
    {
        $db_name = Config::get('attachment.mysql.db_name');
        $this->file = new QueryHelper('attachment_file', $db_name);
    }
}