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
 * MySQL数据库驱动
 * @author Samding
 */
class Driver
{

    /**
     * 连接字符串
     * @var string
     */
    private $dsn;

    /**
     * 用户名
     * @var string
     */
    private $username;

    /**
     * 密码
     * @var string
     */
    private $password;

    /**
     * PDO对象
     * @var \PDO
     */
    private $pdo;

    /**
     * 受影响行数
     * @var int
     */
    private $affected_rows = 0;

    /**
     * 实例
     * @var Driver
     */
    private static $instance = null;

    /**
     * 构造器
     * @param $host
     * @param $port
     * @param $username
     * @param $password
     */
    private function __construct($host, $port, $username, $password)
    {
        $this->pdo = null;
        $this->dsn = "mysql:host={$host};port={$port}";
    }

    /**
     * 获取实例
     * @param array $config
     * @return Driver
     */
    public static function getInstance(array $config = array())
    {
        if (!empty($config)) {
            $host = isset($config['host'])?$config['host']:'localhost';
            $port = isset($config['port'])?$config['port']:3306;
            $username = isset($config['username'])?$config['username']:'root';
            $password = isset($config['password'])?$config['password']:'';
            self::$instance = new Driver($host, $port, $username, $password);
        }
        return self::$instance;
    }

    /**
     * 发起数据库连接
     * @throws \PDOException
     * @param bool $force 为真时强制发起新连接
     */
    private function connect($force = false)
    {
        if ($force || is_null($this->pdo)) {
            $this->pdo = new \PDO($this->dsn, $this->username, $this->password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            $this->pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, true);
        }
    }

    /**
     * 开始事务
     * @throws \PDOException
     * @return bool
     */
    public function beginTrans()
    {
        $this->connect();
        return $this->pdo->beginTransaction();
    }

    /**
     * 提交事务
     * @throws \PDOException
     * @return bool
     */
    public function commitTrans()
    {
        $this->connect();
        return $this->pdo->commit();
    }

    /**
     * 回滚事务
     * @throws \PDOException
     * @return bool
     */
    public function rollbackTrans()
    {
        $this->connect();
        return $this->pdo->rollBack();
    }

    /**
     * 字符串转义
     * @param string $str
     * @return string
     */
    public function escapeString($str)
    {
        return addslashes($str);
    }

    /**
     * 数组转义
     * @param array $array
     * @return array
     */
    public function escapeArray(array $array)
    {
        $output = array();
        $callback = function ($value, $key) use (&$output)
        {
            if (!is_array($value)) {
                $output[$key] = $this->escapeString($value);
            }
            else {
                $output[$key] = $this->escapeArray($value);
            }
        };
        array_walk($array, $callback);

        return $output;
    }

    /**
     * 获取最后一次查询的受影响行数
     * @return int
     */
    public function getAffectedRows()
    {
        return $this->affected_rows;
    }

    /**
     * 获取最后一次插入自增ID
     * @throws \PDOException
     * @return int
     */
    public function getInsertId()
    {
        $this->connect();
        return $this->pdo->lastInsertId();
    }

    /**
     * 为SQL语句绑定参数
     * @param \PDOStatement $statement
     * @param array $params
     * @throws \PDOException
     */
    private function bindParams(\PDOStatement $statement, array $params)
    {
        foreach ($params as $key => $value) {
            //判断数组是普通数组还是关联数组
            if (is_int($key)) {
                //普通数组
                if (is_null($value)) {
                    $statement->bindValue($key+1, null, \PDO::PARAM_NULL);
                }
                elseif ($value < PHP_INT_MAX) {
                    $statement->bindParam($key+1, $value, \PDO::PARAM_INT);
                }
                else {
                    $statement->bindParam($key+1, $value, \PDO::PARAM_STR);
                }
            }
            else {
                //关联数组
                if (is_null($value)) {
                    $statement->bindValue($key, null, \PDO::PARAM_NULL);
                }
                elseif ($value < PHP_INT_MAX) {
                    $statement->bindParam($key, $value, \PDO::PARAM_INT);
                }
                else {
                    $statement->bindParam($key, $value, \PDO::PARAM_STR);
                }
            }
        }
    }

    /**
     * 执行查询
     * @param $sql
     * @param array $params sql语句中要绑定的参数
     * @throws \PDOException
     * @return array
     */
    public function query($sql, array $params = array())
    {
        $this->connect();
        try {
            $statement = $this->pdo->prepare($sql);
            //绑定sql语句参数
            if (!empty($params)) {
                $this->bindParams($statement, $params);
            }
            $statement->execute();
            $this->affected_rows = $statement->rowCount();
            $result = $statement->fetchAll();
        }
        catch (\PDOException $ex) {
            $code = $ex->getCode();
            if ($code == 2006) {
                $this->connect(true);
                return $this->query($sql);
            }
            else {
                throw $ex;
            }
        }
        return empty($result)?array():$result;
    }

    /**
     * 执行SQL语句返回受影响行数
     * @param $sql
     * @param array $params
     * @throws \PDOException
     * @return int
     */
    public function execute($sql, array $params = array())
    {
        $this->query($sql, $params);
        return $this->affected_rows;
    }
}