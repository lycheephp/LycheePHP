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
        //查看当前组件
        $convention_config = array();
        $root_path = LYCHEE_ROOT;//搜索路径
        $handle = opendir($root_path);
        $ignore_dir = array('.', '..');
        while ($file = readdir($handle)) {
            if (!in_array($file, $ignore_dir)) {
                $path = $root_path . DIRECTORY_SEPARATOR . $file;
                //只扫描文件夹
                if (!is_dir($path)) {
                    continue;
                }
                //读取该组件的默认配置
                $config_path = $path . DIRECTORY_SEPARATOR . 'convention.php';
                $key = strtolower($file);
                $convention_config[$key] = include $config_path;
            }
        }
        closedir($handle);

        //检查传入的配置
        if (!isset($config['base'])) {
            $config['base'] = $config;
        }
        //合并配置
        $lychee_config = array_merge($convention_config, $config);
        $base_convention = $lychee_config['base'];
        $output = array();
        $callback = function ($value, $key) use (&$output, $base_convention)
        {
            $output[$key] = array_merge($base_convention, $value);
        };
        array_walk($lychee_config, $callback);
        $lychee_config = $output;
        self::$data = $lychee_config;
        self::$is_load = true;
    }

    /**
     * 获取配置值
     * @param string $name 配置项
     * @param mixed $default 无此项目时返回该参数，默认为null
     * @return mixed
     */
    public static function get($name, $default = null)
    {
        $path = explode('.', $name);
        $result = null;
        foreach ($path as $item) {
            if (is_null($result)) {
                $result = isset(self::$data[$item])?self::$data[$item]:null;
            }
            else {
                $result = isset($result[$item])?$result[$item]:null;
            }
            if (is_null($result)) {
                $result = $default;
                break;
            }
        }
        return $result;
    }
}