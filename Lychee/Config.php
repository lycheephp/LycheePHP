<?php
/**
 * 荔枝类库配置类
 * @author Samding
 */
namespace Lychee;

class Config
{

    /**
     * 标记配置是否已加载
     * @var bool
     */
    private static $is_load = false;

    /**
     * 配置信息
     * @var array
     */
    private static $data = array();

    private function __construct()
    {

    }

    /**
     * 加载配置信息
     * @param array $config
     */
    public static function load(array $config)
    {
        //已经加载过配置则退出
        if (self::$is_load) {
            return;
        }
        //TODO

        self::$is_load = true;
    }

    /**
     * 获取配置值
     * @param string $category 分类
     * @param string $name 配置项
     * @param mixed $default 无此项目时返回该参数，默认为null
     */
    public static function get($category, $name, $default = null)
    {

    }
}