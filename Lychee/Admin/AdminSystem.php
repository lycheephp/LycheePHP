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
use Lychee\Base\MySQL\Operator as Operator;

/**
 * 后台管理系统逻辑类
 * @author Samding
 * @package Lychee\Admin
 */
class AdminSystem
{

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
     * 整理菜单树
     * @param int $parent_id
     * @param array $list
     * @return array
     */
    private static function arrangeMenu($parent_id, array $list)
    {
        $result = array();
        foreach ($list as $info) {
            $temp = $info;
            if ($info['parent_id'] == $parent_id) {
                $temp['children'] = self::arrangeMenu($temp['menu_id'], $list);
                $result[] = $temp;
            }
        }
        return $result;
    }

    /**
     * 以树状图方式获取全部菜单
     * @return array
     */
    public function getAllMenuTree()
    {
        $menu_list = $this->admin_menu->order(array('sort', 'menu_id'), Operator::SORT_ASC)->select();
        return self::arrangeMenu(0, $menu_list);
    }

    /**
     * 获取角色权限
     * @param int $role_id
     * @return array
     */
    public function getRolePrivilege($role_id)
    {
        $role_id = intval($role_id);
        if ($role_id < 1) {
            return array();
        }
        $list = $this->admin_privilege->where(array('role_id' => $role_id))->select();
        $output = array();
        foreach ($list as $info) {
            $output[] = $info['menu_id'];
        }
        return $output;
    }

    /**
     * 根据会员以树状图方式获取菜单
     * @param int $admin_id
     * @return array
     */
    public function getMenuTree($admin_id)
    {
        $admin_id = intval($admin_id);
        if ($admin_id < 1) {
            return array();
        }
        $admin_info = $this->admin->where(array('status' => 1, 'admin_id' => $admin_id))->field('role_id')->select(true);
        if (empty($admin_info)) {
            return array();
        }
        $role_id = $admin_info['role_id'];
        $menu_id_list = $this->admin_privilege->where(array('role_id' => $role_id))->select();
        if (empty($menu_id_list)) {
            return array();
        }
        $output = array();
        foreach ($menu_id_list as $row) {
            $output[] = $row['menu_id'];
        }
        $condition = array('menu_id' => array(Operator::QUERY_IN => $output));
        $menu_list = $this->admin_menu->where($condition)->order(array('sort', 'menu_id'), Operator::SORT_ASC)->select();
        return self::arrangeMenu(0, $menu_list);
    }

    /**
     * 检查权限
     * @param int $admin_id
     * @param string $code
     * @return bool
     */
    public function checkPrivilege($admin_id, $code)
    {
        $admin_id = intval($admin_id);
        if ($admin_id < 1) {
            return false;
        }
        $code = trim($code);
        if (empty($code)) {
            return false;
        }
        $admin_info = $this->admin->where(array('status' => 1, 'admin_id' => $admin_id))->field('role_id')->select(true);
        if (empty($admin_info)) {
            return false;
        }
        $role_id = $admin_info['role_id'];
        $menu_info = $this->admin_menu->where(array('code' => $code))->select(true);
        if (empty($menu_info)) {
            return false;
        }
        $count = $this->admin_privilege->where(array('role_id' => $role_id, 'menu_id' => $menu_info['menu_id']))->
                count();
        return $count != 0;
    }

    /**
     * 分配权限
     * @param int $role_id
     * @param array $menu_ids
     * @return int
     */
    public function assignPrivilege($role_id, array $menu_ids)
    {
        $role_id = intval($role_id);
        if ($role_id < 1) {
            return 0;
        }
        $this->admin_privilege->where(array('role_id' => $role_id))->delete();
        $flag = 0;
        foreach ($menu_ids as $menu_id) {
            $data = array();
            $data['role_id'] = $role_id;
            $data['menu_id'] = $menu_id;
            $flag += $this->admin_privilege->data($data)->insert();
        }
        return $flag;
    }

    /**
     * 移除后台管理菜单
     * @param int $menu_id
     * @return int
     */
    public function deleteMenu($menu_id)
    {
        $menu_id = intval($menu_id);
        if ($menu_id < 1) {
            return 0;
        }
        $count = $this->admin_menu->where(array('parent_id' => $menu_id))->count();
        if ($count != 0) {
            return 0;
        }
        $condition = array('menu_id' => $menu_id);
        $this->admin_privilege->where($condition)->delete();
        return $this->admin_menu->where($condition)->delete();
    }

    /**
     * 获取菜单信息
     * @param int $menu_id
     * @return array
     */
    public function getMenuInfo($menu_id)
    {
        $menu_id = intval($menu_id);
        if ($menu_id < 1) {
            return array();
        }
        return $this->admin_menu->where(array('menu_id' => $menu_id))->select(true);
    }

    /**
     * 获取菜单列表
     * @return array
     */
    public function getMenuList()
    {
        return $this->admin_menu->order(array('sort', 'menu_id'), Operator::SORT_ASC)->select();
    }

    /**
     * 判断菜单代码是否存在
     * @param $code
     * @return bool
     */
    public function isCodeExist($code)
    {
        $code = trim($code);
        $code = strtolower($code);
        return $this->admin_menu->where(array('code' => $code))->count() != 0;
    }

    /**
     * 添加菜单
     * @param array $data
     * @return int
     */
    public function addMenu(array $data)
    {
        return $this->admin_menu->data($data)->insert();
    }

    /**
     * 更新菜单
     * @param array $data
     * @param int $menu_id
     * @return int
     */
    public function editMenu(array $data, $menu_id)
    {
        $menu_id = intval($menu_id);
        if ($menu_id < 1) {
            return 0;
        }
        return $this->admin_menu->data($data)->where(array('menu_id' => $menu_id))->update();
    }

}