<?php
/**
 * 荔枝类库入口脚本
 * 执行必须初始化工作
 * @author Samding
 */
namespace Lychee;

defined('LYCHEE_ROOT') || define('LYCHEE_ROOT', dirname(__FILE__));
include LYCHEE_ROOT .DIRECTORY_SEPARATOR . 'Autoload.php';

//初始化自动加载
Autoload::init();

/**
 * 便捷函数
 * 传入配置数组快速初始化荔枝
 * @param array $config
 */
function lychee_init(array $config = array())
{
    Config::load($config);
}