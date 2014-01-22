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
namespace Lychee\Admin;

use Lychee\Config as Config;
use Lychee\Base\MySQL\QueryHelper as QueryHelper;

/**
 * 后台系统用户逻辑类
 * @author Samding
 * @package Lychee\Admin
 */
class AdminUser
{

    /**
     * 后台管理员表查询类
     * @var QueryHelper
     */
    private $admin;

    /**
     * 后台管理菜单表查询类
     * @var QueryHelper
     */
    private $admin_menu;

    /**
     * 后台管理权限表
     * @var QueryHelper
     */
    private $admin_privilege;

    /**
     * 后台管理角色表
     * @var QueryHelper
     */
    private $admin_role;

    /**
     * 构造器
     */
    public function __construct()
    {
        $db_name = Config::get('admin.mysql.db_name');
        $this->admin = new QueryHelper('admin', $db_name);
        $this->admin_menu = new QueryHelper('admin_menu', $db_name);
        $this->admin_privilege = new QueryHelper('admin_privilege', $db_name);
        $this->admin_role = new QueryHelper('admin_role', $db_name);
    }

    /**
     * 用户名是否存在
     * @param string $username
     * @return bool
     */
    public function isUsernameExist($username)
    {
        $username = trim($username);
        $flag = $this->admin->where(array('username' => $username))->count() != 0;
        return $flag;
    }

    /**
     * 生成哈希盐
     * @return string
     */
    private static function generateSalt()
    {
        return md5(mt_rand());
    }

    /**
     * 生成密码哈希值
     * @param string $password
     * @param string $salt
     * @return string
     */
    private static function generateHash($password, $salt)
    {
        $temp = substr(md5($password), 16);
        return md5($temp . $salt);
    }

    /**
     * 用户注册
     * @param string $username
     * @param string $password
     * @param int $role_id
     * @return int
     */
    public function register($username, $password, $role_id)
    {
        $flag = $this->isUsernameExist($username);
        if ($flag) {
            return -1;//用户名已存在
        }
        $flag = $this->admin_role->where(array('role_id' => $role_id))->count() == 0;
        if (!$flag) {
            return -2;//不存在的角色
        }
        $salt = self::generateSalt();
        $hash = self::generateHash($password, $salt);
        $data['role_id'] = $role_id;
        $data['username'] = $username;
        $data['hash'] = $hash;
        $data['salt'] = $salt;
        $data['status'] = 1;
        return $this->admin->data($data)->insert();
    }

    /**
     * 验证用户登录凭据
     */
    public function auth($username, $password)
    {

    }

    /**
     * 解除用户冻结
     */
    public function unblock()
    {

    }

    /**
     * 冻结用户
     */
    public function block()
    {

    }

    /**
     * 移除用户
     */
    public function removeUser()
    {

    }

    /**
     * 移除角色
     */
    public function removeRole()
    {

    }

}