-- ----------------------------
-- Table structure for archive
-- ----------------------------
DROP TABLE IF EXISTS `archive`;
CREATE TABLE `archive` (
  `archive_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章ID',
  `cate_id` int(10) unsigned NOT NULL COMMENT '文章分类ID',
  `title` varchar(100) NOT NULL COMMENT '文章标题',
  `author` varchar(50) NOT NULL COMMENT '作者',
  `content` text NOT NULL COMMENT '文章内容',
  `click` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击数',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后修改时间',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '降序排序',
  `status` tinyint(3) unsigned NOT NULL COMMENT '0:待审核 1:已审核 2:已删除',
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for archive_category
-- ----------------------------
DROP TABLE IF EXISTS `archive_category`;
CREATE TABLE `archive_category` (
  `cate_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章分类ID',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `name` varchar(50) NOT NULL COMMENT '分类名称',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '降序排序',
  PRIMARY KEY (`cate_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;