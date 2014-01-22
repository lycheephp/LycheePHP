-- ----------------------------
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `admin_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `role_id` int(10) unsigned NOT NULL COMMENT '管理员角色ID',
  `username` varchar(20) NOT NULL COMMENT '管理员帐号',
  `hash` char(32) CHARACTER SET latin1 NOT NULL COMMENT '密码哈希值',
  `salt` char(10) CHARACTER SET latin1 NOT NULL COMMENT '哈希盐',
  `add_time` int(10) unsigned NOT NULL COMMENT '注册时间',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0:未启用 1:启用',
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for admin_menu
-- ----------------------------
DROP TABLE IF EXISTS `admin_menu`;
CREATE TABLE `admin_menu` (
  `menu_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '后台菜单ID',
  `parent_id` int(10) unsigned NOT NULL COMMENT '父节点ID',
  `name` varchar(20) NOT NULL COMMENT '后台菜单名称',
  `code` varchar(20) NOT NULL COMMENT '后台菜单代码',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '降序排序',
  PRIMARY KEY (`menu_id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for admin_privilege
-- ----------------------------
DROP TABLE IF EXISTS `admin_privilege`;
CREATE TABLE `admin_privilege` (
  `role_id` int(10) unsigned NOT NULL COMMENT '角色ID',
  `menu_id` int(10) unsigned NOT NULL COMMENT '菜单ID',
  PRIMARY KEY (`role_id`,`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for admin_role
-- ----------------------------
DROP TABLE IF EXISTS `admin_role`;
CREATE TABLE `admin_role` (
  `role_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '角色ID',
  `name` varchar(20) NOT NULL COMMENT '角色名称',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '降序排序',
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;