<?php
/**
 * Copyright 2013 henryzengpn(Henryzeng) koboshi(Samding)
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
namespace Lychee\Base;

use Lychee;

/**
 * logger
 * @author Samding
 * @package Lychee\Base\Log
 */
class Logger
{

    const EMERGENCY = 'emergency';

    const ALERT = 'alert';

    const CRITICAL = 'critical';

    const ERROR = 'error';

    const WARNING = 'warning';

    const NOTICE = 'notice';

    const INFO = 'info';

    const DEBUG = 'debug';

    private static $log_delimiter = "|----------------------------------------------------------";

    /**
     * 日志路径
     * @var string
     */
    private $log_dir;

    /**
     * 构造器
     * @param string $log_dir
     */
    public function __construct($log_dir)
    {
        $this->log_dir = $log_dir;
    }

    /**
     * 获取日志文件路径
     * @param $level
     * @return string
     */
    private function getLogFilePath($level)
    {
        $datetime = date('Y-m-d'); //获取当前日期
        $log_path = $this->log_dir . DIRECTORY_SEPARATOR . $datetime . DIRECTORY_SEPARATOR . $level . '.txt';
        return $log_path;
    }

    /**
     * 写入日志
     * @param $log_path
     * @param array $lines
     */
    private function write($log_path, array $lines)
    {
        //判断目录是否存在
        $dir_path = dirname($log_path);
        if (!file_exists($dir_path)) {
            mkdir($dir_path, 0777, true);
        }
        //写入信息
        $handle = fopen($log_path, 'a');
        flock($handle, LOCK_EX);
        $lines[] = self::$log_delimiter . PHP_EOL . PHP_EOL . PHP_EOL;
        foreach ($lines as $line) {
            $lines .= PHP_EOL;
            fwrite($handle, $line);
        }
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    /**
     * 获取$_GET变量数据
     * @return string
     */
    private function getGetStr()
    {
        $result = array();
        foreach ($_GET as $key => $value) {
            $result[] = "{$key}={$value}";
        }
        return implode(', ', $result);
    }

    /**
     * 获取$_POST变量数据
     */
    private function getPostStr()
    {
        $result = array();
        foreach ($_POST as $key => $value) {
            $result[] = "{$key}={$value}";
        }
        return implode(', ', $result);
    }

    /**
     * 获取$_SESSION变量数据
     * @return string
     */
    private function getSessionStr()
    {
        $result = array();
        foreach ($_SESSION as $key => $value) {
            $result[] = "{$key}={$value}";
        }
        return implode(', ', $result);
    }

    /**
     * 获取$_COOKIE数据
     * @return string
     */
    private function getCookieStr()
    {
        $result = array();
        foreach ($_COOKIE as $key => $value) {
            $result[] = "{$key}={$value}";
        }
        return implode(', ', $result);
    }

    /**
     * 获取上下文字符串
     * @param array $context
     * @return string
     */
    private function getContextStr(array $context)
    {
        $result = array();
        foreach ($context as $key => $value) {
            $result[] = "{$key}:{$value}";
        }
        return implode(', ', $result);
    }

    /**
     * 紧急
     * @param string $message
     * @param array $context
     */
    public function emergency($message, array $context = array())
    {
        $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * 警告
     * @param string $message
     * @param array $context
     */
    public function alert($message, array $context = array())
    {
        $this->log(self::ALERT, $message, $context);
    }

    /**
     * 危险
     * @param string $message
     * @param array $context
     */
    public function critical($message, array $context = array())
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * 错误
     * @param string $message
     * @param array $context
     */
    public function error($message, array $context = array())
    {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * 警告
     * @param string $message
     * @param array $context
     */
    public function warning($message, array $context = array())
    {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * 提示
     * @param string $message
     * @param array $context
     */
    public function notice($message, array $context = array())
    {
        $this->log(self::NOTICE, $message, $context);
    }

    /**
     * 信息
     * @param string $message
     * @param array $context
     */
    public function info($message, array $context = array())
    {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * 调试
     * @param string $message
     * @param array $context
     */
    public function debug($message, array $context = array())
    {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * 日志
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = array())
    {
        $level = strtolower($level);
        $level = trim($level);
        //生成日志信息
        $lines = array();
        $lines[] = "LEVEL:" . $level;
        $lines[] = "TIME:" . date("Y-m-d H:i:s");
        $lines[] = "HTTP_REFERER:" . $_SERVER['HTTP_REFERER'];
        $lines[] = "GET:" . $this->getGetStr();
        $lines[] = "POST:" . $this->getPostStr();
        $lines[] = "SESSION:" . $this->getSessionStr();
        $lines[] = "COOKIE:" . $this->getCookieStr();
        $lines[] = "MESSAGE:" . $message;
        if (!empty($context)) {
            $lines[] = "CONTEXT:" . $this->getContextStr($context);
        }
        $log_path = $this->getLogFilePath($level);
        $this->write($log_path, $lines);
    }

}