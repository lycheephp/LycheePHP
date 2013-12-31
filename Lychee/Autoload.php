<?php
/**
 * 自动加载类
 * @author Samding
 */
namespace Lychee;

class Autoload
{
    /**
     * 不允许实例化
     */
    private function __construct()
    {

    }

    /**
     * 开始执行自动加载
     */
    public static function init()
    {
        $autoload = function($class_name)
        {
            $namespace = __NAMESPACE__;//类库根命名空间
            if (strpos($class_name, $namespace) === 0) {
                //加载的类属于该类库
                $temp = substr($class_name, strlen($namespace) + 1);
                $class_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $temp) . '.php';
                if (file_exists($class_path)) {
                    require $class_path;
                }
            }
        };
        spl_autoload_register($autoload);
    }
}