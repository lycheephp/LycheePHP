-- ----------------------------
-- Table structure for attachment_file
-- ----------------------------
DROP TABLE IF EXISTS `attachment_file`;
CREATE TABLE `attachment_file` (
  `file_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module_type_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属模块类型id',
  `module_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属模块ID',
  `path` varchar(255) NOT NULL COMMENT '文件路径',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '降序排序',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '0:禁用 1:启用',
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
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '0:禁用 1:启用',
  PRIMARY KEY (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for attachment_image_albumn
-- ----------------------------
DROP TABLE IF EXISTS `attachment_image_albumn`;
CREATE TABLE `attachment_image_albumn` (
  `albumn_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '相册名称',
  `desc` text NOT NULL COMMENT '相册描述',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '0:禁用 1:启用',
  PRIMARY KEY (`albumn_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
