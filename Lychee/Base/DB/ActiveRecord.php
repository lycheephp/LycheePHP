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
namespace Lychee\Base\DB;

/**
 * ActiveRecord实现
 * @author Samding
 * @package Lychee\Base\DB
 */
class ActiveRecord
{

    /**
     * 数据库适配器
     * @var Driver
     */
    private $driver;

    /**
     * 数据库名
     * @var string
     */
    protected $db_name;

    /**
     * 表名
     * @var string
     */
    protected $tbl_name;

    /**
     * SQL语句参数
     * @var array
     */
    private $params;

    /**
     * 数据
     * @var array
     */
    private $data;

    /**
     * where子句
     * @var string
     */
    private $where_str;

    /**
     * join子句
     * @var array
     */
    private $join_str;

    /**
     * limit子句
     * @var string
     */
    private $limit_str;

    /**
     * order子句
     * @var string
     */
    private $order_str;

    /**
     * group子句
     * @var string
     */
    private $group_str;

    /**
     * select 子句
     * @var string
     */
    private $field_str;

    /**
     * 最近一次sql语句
     * @var string
     */
    private $last_sql;

    /**
     * 构造器
     * @param string $db_name
     * @param string $tbl_name
     */
    public function __construct($db_name, $tbl_name)
    {
        $this->driver = Driver::getInstance();
        $this->db_name = $db_name;
        $this->tbl_name = $tbl_name;
        $this->reset();
    }

    /**
     * 重置参数
     */
    private function reset()
    {
        $this->params = array();
        $this->data = array();
        $this->where_str = '';
        $this->join_str = array();
        $this->limit_str = '';
        $this->order_str = '';
        $this->group_str = '';
        $this->field_str = '';
    }

    /**
     * 转义字符串
     * @param string $str
     * @return string
     */
    protected function escapeString($str)
    {
        $result = $this->driver->escapeString($str);
        return $result;
    }

    /**
     * 转义数组
     * @param array $data
     * @return array
     */
    protected function escapeArray(array $data)
    {
        return $this->driver->escapeArray($data);
    }

    /**
     * 获取最后插入id值
     * @throws \PDOException
     * @return int
     */
    protected function getLastID()
    {
        return $this->driver->getInsertId();
    }

    /**
     * 获取受影响行数
     * @return int
     */
    protected function getAffectRows()
    {
        return $this->driver->getAffectedRows();
    }

    /**
     * 获取from子句
     * @return string
     */
    private function getFromStr()
    {
        $result = "`{$this->tbl_name}`";
        if (!empty($this->db_name)) {
            $result = "`{$this->db_name}`.{$result}";
        }
        return $result;
    }

    /**
     * 设置参数值
     * @param array $data
     * @return array
     */
    private function separateParams(array $data)
    {
        $params = array();
        $callback = function ($value, $key) use (&$params, &$data)
        {
            $param_name = ":{$key}";
            $param_value = $value;
            $params[$param_name] = $param_value;

            $data[$key] = $param_name;
        };
        array_walk($data, $callback);
        $this->params = $params;
        return $data;
    }

    /**
     * 获取数据设置子句
     * 关联数组转换成xxx=xxx, yyy=yyy的sql语句形式
     * @param array $data
     * @return string
     */
    private function getDataStr(array $data)
    {
        $output = array();
        $callback = function($value, $key) use (&$output)
        {
            if (strpos($value, ':') === 0) {
                $output[] = "`{$key}` = {$value}";
            }
            else {
                $output[] = "`{$key}` = '{$value}'";
            }

        };
        array_walk($data, $callback);
        return implode(', ', $output);
    }

    /**
     * 执行sql查询
     * 返回查询数据
     * @param string $sql
     * @param array $params
     * @throws \PDOException
     * @return array
     */
    public function query($sql, array $params = array())
    {
        $this->last_sql = $sql;
        $result = $this->driver->query($sql, $params);
        $this->reset();
        return $result;
    }

    /**
     * 执行sql
     * 返回影响行数
     * @param string $sql
     * @param array $params
     * @throws \PDOException
     * @return int
     */
    public function execute($sql, array $params = array())
    {
        $this->last_sql = $sql;
        $result = $this->driver->execute($sql, $params);
        $this->reset();
        return $result;
    }

    /**
     * 开始事务
     * @return bool
     */
    public function begin()
    {
        return $this->driver->beginTrans();
    }

    /**
     * 提交事务
     * @return bool
     */
    public function commit()
    {
        return $this->driver->commitTrans();
    }

    /**
     * 查询总数
     * @throws \PDOException
     * @return int
     */
    public function count()
    {
        $this->field('count(*) AS count', false);
        $list = $this->selectRow();
        $result = 0;
        if (count($list) == 1) {
            $result = intval($list[0]['count']);
        }
        elseif (count($list) > 1) {
            $result = array();
            foreach ($list as $row) {
                $result[] = $row['count'];
            }
        }
        return $result;
    }

    /**
     * 设置数据
     * @param array $data
     * @throws \PDOException
     * @return ActiveRecord
     */
    public function data(array $data)
    {
        if (empty($data)) {
            $this->data = array();
        }
        else {
            $this->data = $data;
        }
        return $this;
    }

    /**
     * 删除记录
     * @throws \PDOException
     * @return int
     */
    public function delete()
    {
        if (empty($this->where_str)) {
            return 0;
        }
        $from_str = $this->getFromStr();
        $sql = "DELETE FROM {$from_str} WHERE {$this->where_str}";
        if (!empty($this->limit_str)) {
            $sql .= " LIMIT {$this->limit_str}";
        }
        $result = $this->execute($sql);
        return $result;
    }

    /**
     * 设置查询字段
     * @param array|string $field
     * @param bool $secure
     * @return ActiveRecord
     */
    public function field($field, $secure = true)
    {
        if (empty($field)) {
            $this->field_str = '*';
            return $this;
        }
        if (is_array($field)) {
            $output = array();
            foreach ($field as $item) {
                if ($secure) {
                    $item = $this->escapeString($item);
                    $output[] = "`{$item}`";
                }
                else {
                    $output[] = $item;
                }
            }
            $field = implode(', ', $output);
        }
        else {
            if ($secure) {
                $field = "`{$this->escapeString($field)}`";
            }
        }
        $this->field_str = $field;
        return $this;
    }

    /**
     * 获取数据库名称
     * @return string
     */
    public function getDBName()
    {
        return $this->db_name;
    }

    /**
     * 获取最近一次执行的SQL
     * @return string
     */
    public function getLastSQL()
    {
        return $this->last_sql;
    }

    /**
     * 获取表名
     * @return string
     */
    public function getTblName()
    {
        return $this->tbl_name;
    }

    /**
     * 分组
     * 例子1:group('id') //GROUP BY id
     * 例子2:group(array('id', 'name')) //GROUP BY id, name
     * @param array|string $field
     * @return ActiveRecord
     */
    public function group($field)
    {
        if (empty($field)) {
            $this->group_str = '';
            return $this;
        }
        if (is_array($field)) {
            $output = array();
            foreach ($field as $item) {
                $output[] = "`{$item}`";
            }
            $field = implode(', ', $field);
        }
        else {
            $field = $this->escapeString($field);
        }
        $this->group_str = $field;
        return $this;
    }

    /**
     * 插入记录
     * @throws \PDOException
     * @return int
     */
    public function insert()
    {
        $data = $this->data;
        if (empty($data)) {
            return 0;
        }
        $data = $this->separateParams($data);
        $field_str = $this->getDataStr($data);
        $from_str = $this->getFromStr();
        $sql = "INSERT INTO {$from_str} SET {$field_str}";
        $this->execute($sql, $this->params);
        return $this->getLastID();
    }

    /**
     * 连接表
     * 例子1: join('id' => 'tbl_b', 'id') // INNER JOIN tbl_b ON tbl_b.id = id
     * 例子2: join('id' => 'tbl_b', 'id' => 'tbl_c', self::LEFT_JOIN) // LEFT JOIN tbl_b ON tbl_b.id = tbl_c.id
     * @param array $condition
     * @param string $type 链接类型
     * @return ActiveRecord
     */
    public function join(array $condition, $type = Operator::JOIN_INNTER)
    {
        if (empty($condition)) {
            return $this;
        }
        //获取要链接的表名
        $target_str = '';
        foreach ($condition as $value) {
            $target_str = $this->escapeString($value);
            break;
        }
        if (strpos($target_str, '.') !== false) {
            $target_str = preg_replace("/^(.*?)\.(.*?)$/is", "`$1`.`$2`", $target_str);
        }
        else {
            $target_str = "`{$target_str}`";
        }
        //获取链接条件
        $on_str = array();
        $callback = function ($value, $key) use (&$on_str, &$target_str)
        {
            if (is_int($key)) {
                $value = $this->escapeString($value);
                $on_str[] = "`$target_str`.`{$value}`";
            }
            else {
                $value = $this->escapeString($value);
                $key = $this->escapeString($key);
                $on_str[] = "`{$value}`.`{$key}`";
            }
        };
        array_walk($condition, $callback);
        $on_str = implode(' = ', $on_str);
        $type = ltrim($type, '$');
        $result = "{$type} {$target_str} ON {$on_str}";
        $this->join_str[] = $result;
        return $this;
    }

    /**
     * 设置limit条件
     * 例子1: limit(1) //LIMIT 1
     * 例子2: limit(1, 2) //LIMIT 2, 1(OFFSET 2 LIMIT 1)
     * @param int $limit
     * @param int $offset
     * @return ActiveRecord
     */
    public function limit($limit, $offset = 0)
    {
        $limit = intval($limit);
        $offset = intval($offset);
        $this->limit_str = "{$offset}, {$limit}";
        return $this;
    }

    /**
     * 查询最大值
     * @param string $field
     * @throws \PDOException
     * @return int|array
     */
    public function max($field)
    {
        $this->field("MAX(`{$field}``) AS max", false);
        $list = $this->selectRow();
        $result = 0;
        if (count($list) == 1) {
            $result = intval($list[0]['max']);
        }
        elseif (count($list) > 1) {
            $result = array();
            foreach ($list as $row) {
                $result[] = $row['max'];
            }
        }
        return $result;
    }

    /**
     * 查询最小值
     * @param string $field
     * @throws \PDOException
     * @return int|array
     */
    public function min($field)
    {
        $this->field("min({$field}) AS min", false);
        $list = $this->selectRow();
        $result = 0;
        if (count($list) == 1) {
            $result = intval($list[0]['min']);
        }
        elseif (count($list) > 1) {
            $result = array();
            foreach ($list as $row) {
                $result[] = $row['min'];
            }
        }
        return $result;
    }

    /**
     * 排序
     * 例子1:order('id') //ORDER BY id DESC
     * 例子2:order('id', Operator::SORT_ASC)//ORDER BY id ASC
     * 例子3:order(array('add_time', 'id'))//ORDER BY add_time, id DESC
     * @param string|array $field
     * @param Operator|string $direction
     * @return ActiveRecord
     */
    public function order($field, $direction = Operator::SORT_DESC)
    {
        if (empty($field)) {
            $this->order_str = '';
            return $this;
        }
        if (!empty($direction)){
            $direction = ltrim($direction, '$');
        }
        if (is_array($field)) {
            $output = array();
            foreach ($field as $item) {
                $output[] = "`{$item}`";
            }
            $field = implode(', ', $field);
        }
        else {
            $field = $this->escapeString($field);
        }
        $this->order_str = "{$field} {$direction}";
        return $this;
    }

    /**
     * 回滚事务
     * @throws \PDOException
     * @return bool
     */
    public function rollback()
    {
        return $this->driver->rollbackTrans();
    }

    /**
     * 查询行
     * @throws \PDOException
     * @return array
     */
    private function selectRow() {
        $from_str = $this->getFromStr();
        $field = $this->field_str;
        if (empty($field)) {
            $field = '*';
        }
        $sql = "SELECT {$field} FROM {$from_str}";
        if (!empty($this->join_str)) {
            $temp = implode(' ', $this->join_str);
            $sql .= " {$temp}";
        }
        if (!empty($this->where_str)) {
            $sql .= " WHERE {$this->where_str}";
        }
        if (!empty($this->group_str)) {
            $sql .= " GROUP BY {$this->group_str}";
        }
        if (!empty($this->order_str)) {
            $sql .= " ORDER BY {$this->order_str}";
        }
        if (!empty($this->limit_str)) {
            $sql .= " LIMIT {$this->limit_str}";
        }
        $list = $this->query($sql);
        return $list;
    }

    /**
     * 查询数据
     * @param bool $single
     * @throws \PDOException
     * @return array
     */
    public function select($single = false)
    {
        if ($single) {
            $this->limit(1);
        }
        $list = $this->selectRow();
        if ($single) {
            return isset($list[0])?$list[0]:array();
        }
        return $list;
    }

    /**
     * 设置数据库
     * @param string $name
     * @return ActiveRecord
     */
    public function setDBName($name)
    {
        $this->db_name = trim($name);
        return $this;
    }

    /**
     * 设置表名
     * @param string $name
     * @return ActiveRecord
     */
    public function setTblName($name)
    {
        $this->tbl_name = trim($name);
        return $this;
    }

    /**
     * 更新数据
     * @throws \PDOException
     * @return int
     */
    public function update()
    {
        $data = $this->data;
        if (empty($data) || empty($this->where_str)) {
            return 0;
        }
        $data = $this->separateParams($data);
        $field_str = $this->getDataStr($data);
        $from_str = $this->getFromStr();
        $sql = "UPDATE {$from_str} SET {$field_str} WHERE {$this->where_str}";
        if (!empty($this->limit_str)) {
            $sql .= " LIMIT {$this->limit_str}";
        }
        $result = $this->execute($sql, $this->params);
        return $result;
    }

    /**
     * 构建where条件子句片段
     * @param $field
     * @param array $condition
     * @return string
     */
    private function buildWhereFragment($field, array $condition)
    {
        $result = '1=1';
        foreach ($condition as $key => $value) {
            if ($key == Operator::QUERY_BETWEEN) {
                $from = isset($value[0])?$this->escapeString($value[0]):0;
                $to = isset($value[1])?$this->escapeString($value[1]):0;
                $result = "`{$field}` BETWEEN '{$from}' AND '{$to}'";
            }
            elseif ($key == Operator::QUERY_NOT_IN || $key == Operator::QUERY_IN) {
                $type_temp = ltrim($key, '$');
                $output = array();
                $callback = function($value, $key) use (&$output)
                {
                    $value = $this->escapeString($value);
                    $output[$key] = "'$value'";
                };
                array_walk($value, $callback);
                $value_temp = implode(', ', $output);
                $result = "`{$field}` {$type_temp} ({$value_temp})";
            }
            elseif (in_array($key, array(Operator::QUERY_EQUAL, Operator::QUERY_GT, Operator::QUERY_GTE, Operator::QUERY_LT, Operator::QUERY_LTE, Operator::QUERY_NE))) {
                $type_temp = ltrim($key, '$');
                $result = "`$field` {$type_temp} '{$value}'";
            }
            elseif ($key == Operator::QUERY_LIKE) {
                $value = $this->escapeString($value);
                $result = "`{$field}` LIKE '%{$value}%'";
            }
            break;//只检查第一个元素
        }
        return $result;
    }

    /**
     * 构建where条件子句
     * @param array $condition
     * @param string|Operator $type
     * @return string
     */
    private function buildWhereStr(array $condition, $type = Operator::QUERY_AND)
    {
        $result = '1=1';
        $sub_arr = array();
        foreach ($condition as $key => $value) {
            if (strpos($key, '$') !== 0 && !is_array($value)) {
                $key = $this->escapeString($key);
                $value = $this->escapeString($value);
                $sub_arr[] = "`{$key}` = '{$value}'";
            }
            elseif (strpos($key, '$') !== 0 && is_array($value)) {
                $sub_arr[] = $this->buildWhereFragment($key, $value);
            }
            elseif (strpos($key, '$') == 0) {
                $sub_arr[] = $this->buildWhereStr($value, $key);
            }
        }
        $output = array();
        $callback = function ($value, $key) use (&$output)
        {
            $output[$key] = "({$value})";
            //$output[$key] = "{$value}";
        };
        array_walk($sub_arr, $callback);
        $sub_arr = $output;
        if ($type == Operator::QUERY_AND || $type == Operator::QUERY_OR) {
            $temp = ltrim($type, '$');
            $result = implode(" {$temp} ", $sub_arr);
        }

        return $result;
    }

    /**
     * 设置where条件
     * 例子1: where(array('id' => 1)) //WHERE id = 1
     * 例子2: where(array('name' => 'koboshi', 'id' => 2)) //WHERE id = 2 AND name = 'koboshi'
     * 例子3: where(array('age' => array(Operator::BETWEEN = > array('3', '4')))) //WHERE age BETWWEN 3 AND 4
     * 例子4: where(array('age' => array(Operator::BETWEEN = > array('3', '4')), 'name' => array(Operator::IN => array('a', 'b', 'c')))) //WHERE age BETWWEN 3 AND 4 AND name IN ('a', 'b', 'c')
     * 例子5: where(array(Operator::QUERY_OR => array('status' => 1, 'name' => 2), 'id' => 3)) //WHERE (status = 1 OR name = 2) AND id = 3
     * @param array $condition
     * @return ActiveRecord
     */
    public function where(array $condition)
    {
        if (empty($condition)) {
            $this->where_str = '1=1';
            return $this;
        }
        $this->where_str = $this->buildWhereStr($condition);
        return $this;
    }
}