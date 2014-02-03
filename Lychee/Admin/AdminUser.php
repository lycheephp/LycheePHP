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

use Lychee\Base\MySQL\Operator;
use Lychee\Config as Config;
use Lychee\Base\MySQL\QueryHelper as QueryHelper;
use Lychee\Utils\HTTP as HTTP;

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
     * 后台管理员验证日志
     * @var QueryHelper
     */
    private $admin_auth_log;

    /**
     * 构造器
     */
    public function __construct()
    {
        $db_name = Config::get('admin.mysql.db_name');
        $this->admin = new QueryHelper('admin', $db_name);
        $this->admin_privilege = new QueryHelper('admin_privilege', $db_name);
        $this->admin_role = new QueryHelper('admin_role', $db_name);
        $this->admin_auth_log = new QueryHelper('admin_auth_log', $db_name);
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
        if ($flag) {
            return -2;//不存在的角色
        }
        $salt = self::generateSalt();
        $hash = self::generateHash($password, $salt);
        $data['role_id'] = $role_id;
        $data['username'] = $username;
        $data['hash'] = $hash;
        $data['salt'] = $salt;
        $data['add_time'] = time();
        $data['status'] = 1;
        return $this->admin->data($data)->insert();
    }

    /**
     * 更改吗密码
     * @param int $admin_id
     * @param string $old
     * @param string $new
     * @return int
     */
    public function changePassword($admin_id, $old, $new)
    {
        $admin_id = intval($admin_id);
        if ($admin_id < 1) {
            return -1;//不存在该用户
        }
        $admin_info = $this->admin->where(array('admin_id' => $admin_id))->select(true);
        if (empty($admin_info)) {
            return -1;
        }
        $username = $admin_info['username'];
        $flag = $this->auth($username, $old);
        if ($flag < 1) {
            return -2;//旧密码不正确
        }
        return $this->resetPassword($admin_id, $new);
    }

    /**
     * 重设密码
     * @param int $admin_id
     * @param string $password
     * @return int
     */
    public function resetPassword($admin_id, $password)
    {
        $salt = self::generateSalt();
        $hash = self::generateHash($password, $salt);
        $data['salt'] = $salt;
        $data['hash'] = $hash;
        $flag = $this->admin->data($data)->where(array('admin_id' => $admin_id))->update();
        return intval($flag > 0);
    }

    /**
     * 验证用户登录凭据
     * @param string $username
     * @param string $password
     * @return array|int list($admin_id, $role_id, $username)
     */
    public function auth($username, $password)
    {
        //验证日志数据
        $data = array();
        $data['admin_id'] = 0;
        $data['ip'] = HTTP::getClientIP();
        $data['add_time'] =time();
        $user_info = $this->admin->where(array('username' => $username))->select(true);
        if (empty($user_info)) {
            //写入记录
            $data['status'] = 0;
            $this->admin_auth_log->data($data)->insert();
            return -1;//用户不存在
        }
        if ($user_info['status'] == 0) {
            $data['admin_id'] = $user_info['admin_id'];
            $data['status'] = 0;
            $this->admin_auth_log->data($data)->insert();
            return -2;//用户被冻结
        }
        $salt = $user_info['salt'];
        $hash = self::generateHash($password, $salt);
        if ($hash != $user_info['hash']) {
            $data['admin_id'] = $user_info['admin_id'];
            $data['status'] = 0;
            $this->admin_auth_log->data($data)->insert();
            return -3;//密码不正确
        }
        $data['admin_id'] = $user_info['admin_id'];
        $data['status'] = 1;
        $this->admin_auth_log->data($data)->insert();
        return array($user_info['admin_id'], $user_info['role_id'], $user_info['username']);
    }

    /**
     * 获取最近登录凭据验证记录
     * @param int $admin_id
     * @return array
     */
    public function getLastAuthLog($admin_id)
    {
        $admin_id = intval($admin_id);
        if ($admin_id < 1) {
            return array();
        }
        return $this->admin_auth_log->where(array('admin_id' => $admin_id))->order('add_time', Operator::SORT_DESC)->
            select(true);
    }

    /**
     * 解除用户冻结
     * @param int $id
     * @return int
     */
    public function unblock($id)
    {
        $id = intval($id);
        if ($id < 1) {
            return 0;
        }
        return $this->admin->data(array('status' => 1))->where(array('admin_id' => $id))->update();
    }

    /**
     * 冻结用户
     * @param int $id
     * @return int
     */
    public function block($id)
    {
        $id = intval($id);
        if ($id < 1) {
            return 0;
        }
        return $this->admin->data(array('status' => 0))->where(array('admin_id' => $id))->update();
    }

    /**
     * 获取用户信息
     * @param int $admin_id
     * @return arary
     */
    public function getAdminInfo($admin_id)
    {
        $admin_id = intval($admin_id);
        if ($admin_id < 1) {
            return array();
        }
        return $this->admin->where(array('admin_id' => $admin_id))->select(true);
    }

    /**
     * 获取角色信息
     * @param int $role_id
     * @return array
     */
    public function getRoleInfo($role_id)
    {
        $role_id = intval($role_id);
        if ($role_id < 1) {
            return array();
        }
        return $this->admin_role->where(array('role_id' => $role_id))->select(true);
    }

    /**
     * 移除角色
     * @param int $id
     * @param bool $force
     * @return int
     */
    public function removeRole($id, $force = false)
    {
        $id = intval($id);
        if ($id < 1) {
            return 0;
        }
        $user_count = $this->admin->where(array('role_id' => $id))->count();
        if ($user_count && !$force) {
            return -1;//该角色下还有用户
        }
        $this->admin->where(array('role_id' => $id))->delete();//删除该角色下的用户
        $this->admin_privilege->where(array('role_id' => $id))->delete();//删除该角色的权限
        return $this->admin_role->where(array('role_id' => $id))->delete();//删除角色
    }

    /**
     * 获取角色列表
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getRoleList($offset, $limit)
    {
        $result = $this->admin_role->limit($limit, $offset)->order('sort')->select();
        $output = array();
        foreach ($result as $info) {
            $role_id = $info['role_id'];
            $admin_count = $this->admin->where(array('role_id' => $role_id))->count();
            $info['admin_count'] = $admin_count;
            $output[] = $info;
        }
        return $output;
    }

    /**
     * 获取用户列表
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getAdminList($offset, $limit)
    {
        $result = $this->admin->limit($limit, $offset)->order('admin_id', Operator::SORT_ASC)->select();
        $role_cache = array();
        $log_cache = array();
        $output = array();
        foreach ($result as $info) {
            //查询用户角色信息
            $role_id = $info['role_id'];
            if (!isset($role_cache[$role_id])) {
                $role_cache[$role_id] = $this->admin_role->where(array('role_id' => $role_id))->select(true);
            }
            $info['role_name'] = isset($role_cache[$role_id]['name'])?$role_cache[$role_id]['name']:'';
            //查询最后登录信息
            $admin_id = $info['admin_id'];
            if (!isset($log_cache[$admin_id])) {
                $log_cache[$admin_id] = $this->getLastAuthLog($admin_id);
            }
            $info['last_login_ip'] = isset($log_cache[$admin_id]['ip'])?$log_cache[$admin_id]['ip']:'';
            $info['last_login_time'] = isset($log_cache[$admin_id]['add_time'])?$log_cache[$admin_id]['add_time']:'';
            $output[] = $info;
        }
        return $output;
    }

    /**
     * 获取用户总数
     * @return int
     */
    public function getAdminCount()
    {
        return $this->admin->count();
    }

    /**
     * 获取角色总数
     * @return int
     */
    public function getRoleCount()
    {
        return $this->admin_role->count();
    }

}