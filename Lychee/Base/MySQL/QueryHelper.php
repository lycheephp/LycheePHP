<?php
/**
 * Copyright 2013 koboshi(Samding)
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
namespace Lychee\Base\MySQL;

use Lychee\Service as Service;

/**
 * QueryHelper
 * @author Samding
 * @package Lychee\Base\MySQL
 */
class QueryHelper
{

    /**
     * database driver
     * @var Driver
     */
    private $driver;

    /**
     * database name
     * @var string
     */
    protected $db_name;

    /**
     * table name
     * @var string
     */
    protected $tbl_name;

    /**
     * sql statement params
     * @var array
     */
    private $params;

    /**
     * data
     * @var array
     */
    private $data;

    /**
     * where condition
     * @var string
     */
    private $where_str;

    /**
     * join condition list
     * @var array
     */
    private $join_str_list;

    /**
     * limit condition
     * @var string
     */
    private $limit_str;

    /**
     * order by condition
     * @var string
     */
    private $order_str;

    /**
     * group by condition
     * @var string
     */
    private $group_str;

    /**
     * select condition
     * @var string
     */
    private $field_str;

    /**
     * last query(execute) sql
     * @var string
     */
    private $last_sql;

    /**
     * constructor
     * @param string $db_name
     * @param string $tbl_name
     */
    public function __construct($db_name, $tbl_name)
    {
        $this->driver = Service::get('mysql');
        $this->db_name = $db_name;
        $this->tbl_name = $tbl_name;
        $this->reset();
    }

    /**
     * reset all condition
     */
    private function reset()
    {
        $this->params = array();
        $this->data = array();
        $this->where_str = '';
        $this->join_str_list = array();
        $this->limit_str = '';
        $this->order_str = '';
        $this->group_str = '';
        $this->field_str = '';
    }

    /**
     * escape string
     * @param string $str
     * @return string
     */
    protected function escapeString($str)
    {
        $result = $this->driver->escapeString($str);
        return $result;
    }

    /**
     * escape array element
     * @param array $data
     * @return array
     */
    protected function escapeArray(array $data)
    {
        return $this->driver->escapeArray($data);
    }

    /**
     * return last insert id
     * @throws \PDOException
     * @return int
     */
    protected function getLastID()
    {
        return $this->driver->getInsertId();
    }

    /**
     * return affected row
     * @return int
     */
    protected function getAffectRows()
    {
        return $this->driver->getAffectedRows();
    }

    /**
     * return from conditon (`database_a`.`tbl_b`)
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
     * separate data to param
     * array('field' => 'value') ---> array('field' => ':field'), array(':field' => 'value')
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
     * return data setting condition
     * array('field' => 'value') --->  "`field` = 'value'"
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
     * query sql and return result set (assoc array)
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
     * execute sql and return affected row count
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
     * start transaction
     * @return bool
     */
    public function begin()
    {
        return $this->driver->beginTrans();
    }

    /**
     * commit transaction
     * @return bool
     */
    public function commit()
    {
        return $this->driver->commitTrans();
    }

    /**
     * return result set count
     * @throws \PDOException
     * @return int
     */
    public function count()
    {
        $this->field('count(*) AS count', false);
        $list = $this->__select();
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
     * set insert(update) data
     * @param array $data
     * @throws \PDOException
     * @return QueryHelper
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
     * delete record
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
     * set result set column
     * @param array|string $field
     * @param bool $secure
     * @return QueryHelper
     */
    public function field($field, $secure = true)
    {
        if (empty($field)) {
            $this->field_str = '*';
            return $this;
        }
        $output = array();
        $func = function ($value) use (&$output)
        {
            $value = $this->escapeString($value);
            $output[] = "`{$value}`";
        };
        if (is_array($field)) {
            $output = array();
            foreach ($field as $item) {
                if ($secure) {
                    // db.tbl.column -> `db`.`tbl`.`column`
                    if (strpos($item, '.')) {
                        $temp = explode('.', $item);
                        $output = array();
                        array_walk($temp, $func);
                        $item = implode('.', $output);
                    }
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
     * return current database name
     * @return string
     */
    public function getDBName()
    {
        return $this->db_name;
    }

    /**
     * return last query(execute) sql
     * @return string
     */
    public function getLastSQL()
    {
        return $this->last_sql;
    }

    /**
     * return current table name
     * @return string
     */
    public function getTblName()
    {
        return $this->tbl_name;
    }

    /**
     * set group by condition
     * e.g.1:group('id') //GROUP BY id
     * e.g.2:group(array('id', 'name')) //GROUP BY id, name
     * @param array|string $field
     * @return QueryHelper
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
     * insert record
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
     * set join condition
     * e.g.1: join('tbl_c', array('tbl_a.id', 'tbl_c.id')) // INNER JOIN tbl_c ON tbl_a.id = tbl_c.id
     * e.g.2: join(array('tbl_c', array('tbl_a.id', 'tbl_c.id'), Operator::JOIN_LEFT) // LEFT JOIN tbl_c ON tbl_a.id = tbl_c.id
     * @param string $tbl_name
     * @param array $condition
     * @param string $type
     * @return QueryHelper
     */
    public function join($tbl_name, array $condition, $type = Operator::JOIN_INNTER)
    {
        $output = array();
        $func = function ($value) use (&$output)
        {
            $value = $this->escapeString($value);
            $output[] = "`{$value}`";
        };
        $temp = explode('.', $tbl_name);
        $output = array();
        array_walk($temp, $func);
        $tbl_name = implode('.', $output);
        $target_str = $tbl_name;
        $on_str_list = array();
        foreach ($condition as $item) {
            $temp = explode('.', $item);
            $output = array();
            array_walk($temp, $func);
            $on_str_list[] = implode('.', $output);
        }
        $type = ltrim($type, '$');
        $on_str = implode(' = ', $on_str_list);
        $join_str = "{$type} {$target_str} ON {$on_str}";
        $this->join_str_list[] = $join_str;
        return $this;
    }

    /**
     * set limit and offset
     * e.g.1: limit(1) //LIMIT 1
     * e.g.2: limit(1, 2) //LIMIT 2, 1(OFFSET 2 LIMIT 1)
     * @param int $limit
     * @param int $offset
     * @return QueryHelper
     */
    public function limit($limit, $offset = 0)
    {
        $limit = intval($limit);
        $offset = intval($offset);
        $this->limit_str = "{$offset}, {$limit}";
        return $this;
    }

    /**
     * return result set specify column max value
     * @param string $field
     * @throws \PDOException
     * @return int|array
     */
    public function max($field)
    {
        $this->field("MAX(`{$field}``) AS max", false);
        $list = $this->__select();
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
     * return result set specify column min value
     * @param string $field
     * @throws \PDOException
     * @return int|array
     */
    public function min($field)
    {
        $this->field("min({$field}) AS min", false);
        $list = $this->__select();
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
     * set sorting(order by) condition
     * e.g.1:order('id') //ORDER BY id DESC
     * e.g.2:order('id', Operator::SORT_ASC)//ORDER BY id ASC
     * e.g.3:order(array('add_time', 'id'))//ORDER BY add_time, id DESC
     * @param string|array $field
     * @param Operator|string $direction
     * @return QueryHelper
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
     * rollback transaction
     * @throws \PDOException
     * @return bool
     */
    public function rollback()
    {
        return $this->driver->rollbackTrans();
    }

    /**
     * select and return result set (assoc)
     * @throws \PDOException
     * @return array
     */
    private function __select() {
        $from_str = $this->getFromStr();
        $field = $this->field_str;
        if (empty($field)) {
            $field = '*';
        }
        $sql = "SELECT {$field} FROM {$from_str}";
        if (!empty($this->join_str_list)) {
            $temp = implode(' ', $this->join_str_list);
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
     * return result set (assoc)
     * @param bool $single
     * @throws \PDOException
     * @return array
     */
    public function select($single = false)
    {
        if ($single) {
            $this->limit(1);
        }
        $list = $this->__select();
        if ($single) {
            return isset($list[0])?$list[0]:array();
        }
        return $list;
    }

    /**
     * update record
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
     * build where condition fragment
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
     * build where condition
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
     * set where condition
     * e.g.1: where(array('id' => 1)) //WHERE id = 1
     * e.g.2: where(array('name' => 'koboshi', 'id' => 2)) //WHERE id = 2 AND name = 'koboshi'
     * e.g.3: where(array('age' => array(Operator::BETWEEN = > array('3', '4')))) //WHERE age BETWWEN 3 AND 4
     * e.g.4: where(array('age' => array(Operator::BETWEEN = > array('3', '4')), 'name' => array(Operator::IN => array('a', 'b', 'c')))) //WHERE age BETWWEN 3 AND 4 AND name IN ('a', 'b', 'c')
     * e.g.5: where(array(Operator::QUERY_OR => array('status' => 1, 'name' => 2), 'id' => 3)) //WHERE (status = 1 OR name = 2) AND id = 3
     * @param array $condition
     * @return QueryHelper
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