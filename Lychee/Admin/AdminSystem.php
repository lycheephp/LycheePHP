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
    private function arrangeMenu($parent_id, array $list)
    {
        $result = array();
        foreach ($list as $info) {
            $temp = $info;
            if ($info['parent_id'] == $parent_id) {
                $temp['children'] = $this->arrangeCategoryTree($temp['menu_id'], $list);
                $result[] = $temp;
            }
        }
        return $result;
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
        $condition = array('role_id' => array(Operator::QUERY_IN => $output));
        $menu_list = $this->admin_menu->where($condition)->order('sort')->select();
        return self::arrangeMenu(0, $menu_list);
    }

    /**
     * 检查权限
     */
    public function checkPrivilege()
    {

    }

    /**
     * 分配权限
     */
    public function assignPrivilege()
    {

    }

    /**
     * 移除后台管理菜单
     */
    public function removeMenu()
    {

    }

}