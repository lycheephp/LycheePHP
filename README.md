LycheePHP
=========

PHP开源组件

1.0版已完成
带有基础的文章，分类以及附件功能，并带有一个后台管理站点(https://github.com/koboshi/Lychee-Admin)

2.0版筹备中...

后台管理系统手册请参考:https://github.com/koboshi/Lychee-Admin/blob/master/README.md

1.0主要由Admin Archive Attachment三个组件构成,它们互相独立,并依赖于Base组件,Utils组件提供一些常用的工具类

### Base组件

主要包含数据库连接封装,数据查询的助手类以及一个简单文件日志类

### 安装

示例配置

```php
$config = array(
    //数据库配置
    'mysql' => array(
        'host' => '127.0.0.1',
        'port' => 3306,
        'username' => '数据库帐号',
        'password' =>  '数据库密码',
        'db_name' => 'test',
        'charset' => 'utf8',
    ),

    //日志配置
    'logger' => array(
        'log_dir' => LA_ROOT . '/data/log',
    ),
);
```

加载

```php
require '你放置的路径/Lychee.php';
include '上述配置.php'
Lychee\init($config);//完成
```

### MySQL数据库相关示例

假设有如下数据表:

```SQL
DROP TABLE IF EXISTS `test`;
CREATE TABLE `test` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
```

插入:

```PHP
$ar = new MySQL\QueryHelper('test', 'test'); //set database_name and table_name
data = array('name' => 'koboshi', 'status' => 1);
echo $ar->data($data)->insert(); //last insert id '1'
```

更新:

```PHP
$data = ('status' => 2);
echo $ar->data($data)->where(array('id' => 1))->update(); //affected rows 1
```

递增:

```PHP
//UPDATE `test`.`test` SET `status` = `status` + 1 WHERE `name` => 'koboshi';
$ar->where('name' => 'koboshi')->increment('status', 1);
```

递减:

```PHP
//UPDATE `test`.`test` SET `status` = `status` + -1 WHERE `name` => 'koboshi';
$ar->where('name' => 'koboshi')->decrement('status', 1);
```

删除:

```PHP
$condition = array('id' => 2);
echo $ar->where($condition)->delete(); //affected rows 0
```

查询:

```PHP
//SELECT id, name FROM `test`.`test`
$ar->field(array('id', 'name'))->select();

//SELECT name FROM `test`.`test` WHERE id = 1 GROUP BY name ORDER BY id DESC LIMIT 0, 30
$ar->
    field('name')->
    where(array('id' => 1))->
    group('name')->
    order('id', MySQL\Operator::SORT_DESC)->
    limit(30, 0)->
    select();

//SELECT * FROM `test`.`test` WHERE (status = 1 OR name = 2) AND id = 3 GROUP BY id, name
$ar->
    where(array(MySQL\Operator::QUERY_OR => array('status' => 1, 'name' => 2), 'id' => 3))->
    group(array(id, name))->
    select(true);// fetch one row
```

连接:

```PHP
//SELECT tbl_b.id AS b_id, test.id AS t_id FROM `test`.`test` INNER JOIN `tbl_b` ON `test`.`id` = `tbl_b`.`id` LEFT JOIN `tbl_c` ON `tbl_c`.`id` = `tbl_b`.`id`
$ar->
    join('tbl_b' array('tbl_b.id', 'test.id'))->
    join('tbl_c', array('tbl_c.id', 'tbl_b.id'), MySQL\Operator::JOIN_LEFT)->
    field(array('tbl_b.id AS b_id', 'test.id AS t_id'))->
    select();
```

自定义:

```PHP
$sql = "SELECT * FROM `test`.`test` WHERE name = 'koboshi'"
$ar->query($sql);

$sql = "INSERT INTO `test`.`test` (name, status) VALUES ('misha', 2)"
$ar->execute($sql);
```