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
namespace Lychee\Utils;

/**
 * Logger
 * @author Samding
 * @package Lychee\Utils
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

    private static $log_delimiter = '|----------------------------------------------------------';

    /**
     * log directory path
     * @var string
     */
    private $log_dir;

    /**
     * constructor
     * @param string $log_dir
     */
    public function __construct($log_dir)
    {
        $this->log_dir = $log_dir;
    }

    /**
     * return log file path
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
     * write log info
     * @param $log_path
     * @param array $lines
     */
    private function write($log_path, array $lines)
    {
        $dir_path = dirname($log_path);
        if (!file_exists($dir_path)) {
            mkdir($dir_path, 0777, true);
        }
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
     * get array to string
     * @return string
     */
    private function getToStr()
    {
        $result = array();
        foreach ($_GET as $key => $value) {
            $result[] = "{$key}={$value}";
        }
        return implode(', ', $result);
    }

    /**
     * post array to string
     */
    private function postToStr()
    {
        $result = array();
        foreach ($_POST as $key => $value) {
            $result[] = "{$key}={$value}";
        }
        return implode(', ', $result);
    }

    /**
     * session array to string
     * @return string
     */
    private function sessionToStr()
    {
        $result = array();
        foreach ($_SESSION as $key => $value) {
            $result[] = "{$key}={$value}";
        }
        return implode(', ', $result);
    }

    /**
     * cookie array to string
     * @return string
     */
    private function cookieToStr()
    {
        $result = array();
        foreach ($_COOKIE as $key => $value) {
            $result[] = "{$key}={$value}";
        }
        return implode(', ', $result);
    }

    /**
     * convert context object(array) to string
     * @param array $context
     * @return string
     */
    private function contextToStr(array $context)
    {
        $result = array();
        foreach ($context as $key => $value) {
            $result[] = "{$key}:{$value}";
        }
        return implode(', ', $result);
    }

    /**
     * emergency log
     * @param string $message
     * @param array $context
     */
    public function emergency($message, array $context = array())
    {
        $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * alert log
     * @param string $message
     * @param array $context
     */
    public function alert($message, array $context = array())
    {
        $this->log(self::ALERT, $message, $context);
    }

    /**
     * critical log
     * @param string $message
     * @param array $context
     */
    public function critical($message, array $context = array())
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * error log
     * @param string $message
     * @param array $context
     */
    public function error($message, array $context = array())
    {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * warning log
     * @param string $message
     * @param array $context
     */
    public function warning($message, array $context = array())
    {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * notice log
     * @param string $message
     * @param array $context
     */
    public function notice($message, array $context = array())
    {
        $this->log(self::NOTICE, $message, $context);
    }

    /**
     * info log
     * @param string $message
     * @param array $context
     */
    public function info($message, array $context = array())
    {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * debug log
     * @param string $message
     * @param array $context
     */
    public function debug($message, array $context = array())
    {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * log
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = array())
    {
        $level = strtolower($level);
        $level = trim($level);
        //log Info
        $lines = array();
        $lines[] = "LEVEL:" . $level;
        $lines[] = "TIME:" . date("Y-m-d H:i:s");
        $lines[] = "HTTP_REFERER:" . $_SERVER['HTTP_REFERER'];
        $lines[] = "GET:" . $this->getToStr();
        $lines[] = "POST:" . $this->postToStr();
        $lines[] = "SESSION:" . $this->sessionToStr();
        $lines[] = "COOKIE:" . $this->cookieToStr();
        $lines[] = "MESSAGE:" . $message;
        if (!empty($context)) {
            $lines[] = "Context:" . $this->contextToStr($context);
        }
        $log_path = $this->getLogFilePath($level);
        $this->write($log_path, $lines);
    }
}