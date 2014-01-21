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

use Lychee;

/**
 * MySQL Database Driver
 * @author Samding
 * @package Lychee\Base\MySQL
 */
class Driver
{

    /**
     * Data Source Name
     * @var string
     */
    private $dsn;

    /**
     * username
     * @var string
     */
    private $username;

    /**
     * password
     * @var string
     */
    private $password;

    /**
     * PDO instance
     * @var \PDO
     */
    private $pdo;

    /**
     * affected row count
     * @var int
     */
    private $affected_rows = 0;

    /**
     * constructor
     * @param string $host
     * @param int $port
     * @param string $username
     * @param string $password
     * @param string $charset
     */
    public function __construct($host, $port, $username, $password, $charset)
    {
        $this->pdo = null;
        $this->username = $username;
        $this->password = $password;
        $this->dsn = "mysql:host={$host};port={$port};charset={$charset}";
        //$this->dsn = "mysql:host={$host};port={$port}";
    }

    /**
     * initialize connection
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
            $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        }
    }

    /**
     * start transaction
     * @throws \PDOException
     * @return bool
     */
    public function beginTrans()
    {
        $this->connect();
        return $this->pdo->beginTransaction();
    }

    /**
     * commit transaction
     * @throws \PDOException
     * @return bool
     */
    public function commitTrans()
    {
        $this->connect();
        return $this->pdo->commit();
    }

    /**
     * rollback transaction
     * @throws \PDOException
     * @return bool
     */
    public function rollbackTrans()
    {
        $this->connect();
        return $this->pdo->rollBack();
    }

    /**
     * escape string
     * @param string $str
     * @return string
     */
    public function escapeString($str)
    {
        $this->connect();
        if (is_null($str)) {
            $result = $this->pdo->quote($str, \PDO::PARAM_NULL);
        }
        elseif (is_numeric($str) && $str < PHP_INT_MAX) {
            $result = $this->pdo->quote($str, \PDO::PARAM_INT);
        }
        elseif (is_bool($str)) {
            $result = $this->pdo->quote($str, \PDO::PARAM_BOOL);
        }
        else {
            $result = $this->pdo->quote($str, \PDO::PARAM_STR);
        }
        return $result;
    }

    /**
     * escape array element
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
     * return affected row count
     * @return int
     */
    public function getAffectedRows()
    {
        return $this->affected_rows;
    }

    /**
     * return last insert id
     * @throws \PDOException
     * @return int
     */
    public function getInsertId()
    {
        $this->connect();
        return $this->pdo->lastInsertId();
    }

    /**
     * bind params to sql statement
     * @param \PDOStatement $statement
     * @param array $params
     * @throws \PDOException
     */
    private function bindParams(\PDOStatement $statement, array $params)
    {
        foreach ($params as $key => &$value) {
            if (is_int($key)) {
                //array
                if (is_null($value)) {
                    $statement->bindValue($key+1, null, \PDO::PARAM_NULL);
                }
                elseif (is_numeric($value) && $value < PHP_INT_MAX) {
                    $statement->bindParam($key+1, $value, \PDO::PARAM_INT);
                }
                else {
                    $statement->bindParam($key+1, $value, \PDO::PARAM_STR);
                }
            }
            else {
                //assoc array
                if (is_null($value)) {
                    $statement->bindValue($key, null, \PDO::PARAM_NULL);
                }
                elseif (is_numeric($value) && $value < PHP_INT_MAX) {
                    $statement->bindParam($key, $value, \PDO::PARAM_INT);
                }
                else {
                    $statement->bindParam($key, $value, \PDO::PARAM_STR);
                }
            }
        }
    }

    /**
     * query sql and return result set(assoc array)
     * @param $sql
     * @param array $params
     * @throws \PDOException
     * @return array
     */
    public function query($sql, array $params = array())
    {
        $this->connect();
        try {
            $statement = $this->pdo->prepare($sql);
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
     * execute sql and return affected row count
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

    /**
     * close connection
     */
    public function close()
    {
        if (!is_null($this->pdo)) {
            unset($this->pdo);
        }
    }

    /**
     * destructor
     */
    public function __destruct()
    {
        $this->close();
    }
}