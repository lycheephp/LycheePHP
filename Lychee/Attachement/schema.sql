-- ----------------------------
-- Table structure for attachment_file
-- ----------------------------
DROP TABLE IF EXISTS `attachment_file`;
CREATE TABLE `attachment_file` (
  `file_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module_name` varchar(100) NOT NULL DEFAULT '' COMMENT '文件所属模块',
  `module_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件所属模块ID',
  `path` varchar(255) NOT NULL COMMENT '文件路径',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '降序排序',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for attachment_image
-- ----------------------------
DROP TABLE IF EXISTS `attachment_image`;
CREATE TABLE `attachment_image` (
  `image_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `albumn_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '相册id',
  `path` varchar(255) NOT NULL COMMENT '图片路径',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '降序排序',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for atttachment_albumn
-- ----------------------------
DROP TABLE IF EXISTS `atttachment_albumn`;
CREATE TABLE `atttachment_albumn` (
  `albumn_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '相册ID',
  `module_name` varchar(255) NOT NULL DEFAULT '' COMMENT '相册所属模块',
  `module_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '相册所属模块ID',
  `name` varchar(100) NOT NULL COMMENT '相册名称',
  `desc` text NOT NULL COMMENT '相册描述',
  `sort` tinyint(3) unsigned NOT NULL COMMENT '权重，降序',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`albumn_id`),
  UNIQUE KEY `module` (`module_name`,`module_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
