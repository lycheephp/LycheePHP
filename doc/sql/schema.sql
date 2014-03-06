/*
Navicat MySQL Data Transfer

Source Server         : 本地
Source Server Version : 50612
Source Host           : localhost:3306
Source Database       : test

Target Server Type    : MYSQL
Target Server Version : 50612
File Encoding         : 65001

Date: 2014-03-04 21:55:41
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `admin_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `role_id` int(10) unsigned NOT NULL COMMENT '管理员角色ID',
  `username` varchar(20) NOT NULL COMMENT '管理员帐号',
  `hash` char(32) CHARACTER SET latin1 NOT NULL COMMENT '密码哈希值',
  `salt` char(32) CHARACTER SET latin1 NOT NULL COMMENT '哈希盐',
  `add_time` int(10) unsigned NOT NULL COMMENT '注册时间',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0:未启用 1:启用',
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of admin
-- ----------------------------
INSERT INTO `admin` VALUES ('1', '1', 'root', '68392cd34eaeae7a6b355e4b76685f1d', '8d3d436e79900056a84348b7939ffd01', '1390879513', '1');

-- ----------------------------
-- Table structure for admin_auth_log
-- ----------------------------
DROP TABLE IF EXISTS `admin_auth_log`;
CREATE TABLE `admin_auth_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `admin_id` int(10) unsigned NOT NULL COMMENT '管理员ID',
  `ip` varchar(50) NOT NULL COMMENT 'ip地址',
  `add_time` int(10) unsigned NOT NULL COMMENT '记录时间',
  `status` tinyint(3) unsigned NOT NULL COMMENT '0:登录失败 1:登录成功',
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of admin_auth_log
-- ----------------------------

-- ----------------------------
-- Table structure for admin_menu
-- ----------------------------
DROP TABLE IF EXISTS `admin_menu`;
CREATE TABLE `admin_menu` (
  `menu_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '后台菜单ID',
  `parent_id` int(10) unsigned NOT NULL COMMENT '父节点ID',
  `name` varchar(20) NOT NULL COMMENT '后台菜单名称',
  `code` varchar(20) NOT NULL COMMENT '后台菜单代码',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '升序排序',
  PRIMARY KEY (`menu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of admin_menu
-- ----------------------------
INSERT INTO `admin_menu` VALUES ('1', '0', '我的面板', 'panel', '0');
INSERT INTO `admin_menu` VALUES ('2', '0', '内容', 'content', '0');
INSERT INTO `admin_menu` VALUES ('3', '0', '更多', 'more', '0');
INSERT INTO `admin_menu` VALUES ('4', '1', '系统信息', 'sysinfo', '0');
INSERT INTO `admin_menu` VALUES ('5', '1', '个人信息', 'info', '0');
INSERT INTO `admin_menu` VALUES ('6', '4', '概览', 'summary', '0');
INSERT INTO `admin_menu` VALUES ('7', '5', '查看信息', 'admin_info', '0');
INSERT INTO `admin_menu` VALUES ('8', '5', '修改密码', 'change_password', '0');
INSERT INTO `admin_menu` VALUES ('9', '10', '菜单管理', 'menu_list', '0');
INSERT INTO `admin_menu` VALUES ('10', '3', '系统', 'system', '0');
INSERT INTO `admin_menu` VALUES ('11', '10', '管理员管理', 'admin_list', '0');
INSERT INTO `admin_menu` VALUES ('12', '10', '角色管理', 'role_list', '0');
INSERT INTO `admin_menu` VALUES ('13', '2', '文章', 'archive', '0');
INSERT INTO `admin_menu` VALUES ('14', '13', '分类管理', 'category_list', '0');
INSERT INTO `admin_menu` VALUES ('15', '13', '文章管理', 'archive_list', '0');

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
-- Records of admin_privilege
-- ----------------------------
INSERT INTO `admin_privilege` VALUES ('1', '1');
INSERT INTO `admin_privilege` VALUES ('1', '2');
INSERT INTO `admin_privilege` VALUES ('1', '3');
INSERT INTO `admin_privilege` VALUES ('1', '4');
INSERT INTO `admin_privilege` VALUES ('1', '5');
INSERT INTO `admin_privilege` VALUES ('1', '6');
INSERT INTO `admin_privilege` VALUES ('1', '7');
INSERT INTO `admin_privilege` VALUES ('1', '8');
INSERT INTO `admin_privilege` VALUES ('1', '9');
INSERT INTO `admin_privilege` VALUES ('1', '10');
INSERT INTO `admin_privilege` VALUES ('1', '11');
INSERT INTO `admin_privilege` VALUES ('1', '12');
INSERT INTO `admin_privilege` VALUES ('1', '13');
INSERT INTO `admin_privilege` VALUES ('1', '14');
INSERT INTO `admin_privilege` VALUES ('1', '15');

-- ----------------------------
-- Table structure for admin_role
-- ----------------------------
DROP TABLE IF EXISTS `admin_role`;
CREATE TABLE `admin_role` (
  `role_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '角色ID',
  `name` varchar(20) NOT NULL COMMENT '角色名称',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of admin_role
-- ----------------------------
INSERT INTO `admin_role` VALUES ('1', '系统管理员', '1391517557');

-- ----------------------------
-- Table structure for archive
-- ----------------------------
DROP TABLE IF EXISTS `archive`;
CREATE TABLE `archive` (
  `archive_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章ID',
  `cate_id` int(10) unsigned NOT NULL COMMENT '文章分类ID',
  `title` varchar(100) NOT NULL COMMENT '文章标题',
  `author` varchar(50) NOT NULL COMMENT '作者',
  `cover` varchar(255) NOT NULL COMMENT '封面图',
  `content` text NOT NULL COMMENT '文章内容',
  `click` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击数',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后修改时间',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '降序排序',
  `status` tinyint(3) unsigned NOT NULL COMMENT '0:待审核 1:已审核 2:已删除',
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of archive
-- ----------------------------

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

-- ----------------------------
-- Records of archive_category
-- ----------------------------

-- ----------------------------
-- Table structure for attachment_album
-- ----------------------------
DROP TABLE IF EXISTS `attachment_album`;
CREATE TABLE `attachment_album` (
  `album_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '相册ID',
  `module_name` varchar(255) NOT NULL DEFAULT '' COMMENT '相册所属模块',
  `module_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '相册所属模块ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '相册名称',
  `desc` text NOT NULL COMMENT '相册描述',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '权重，降序',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`album_id`),
  UNIQUE KEY `module` (`module_name`,`module_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of attachment_album
-- ----------------------------

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
-- Records of attachment_file
-- ----------------------------

-- ----------------------------
-- Table structure for attachment_image
-- ----------------------------
DROP TABLE IF EXISTS `attachment_image`;
CREATE TABLE `attachment_image` (
  `image_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `album_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '相册id',
  `path` varchar(255) NOT NULL COMMENT '图片路径',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '降序排序',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`image_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of attachment_image
-- ----------------------------

-- ----------------------------
-- Table structure for goods
-- ----------------------------
DROP TABLE IF EXISTS `goods`;
CREATE TABLE `goods` (
  `goods_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cate_id` int(10) unsigned NOT NULL COMMENT '分类ID',
  `name` varchar(100) NOT NULL COMMENT '商品名称',
  `cover` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图',
  `click` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '点击数',
  `cost_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '成本价',
  `net_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '净价',
  `price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '售价',
  `short_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '短介绍',
  `desc` text NOT NULL COMMENT '商品介绍',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0:下架 1:上架',
  PRIMARY KEY (`goods_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for goods_category
-- ----------------------------
DROP TABLE IF EXISTS `goods_category`;
CREATE TABLE `goods_category` (
  `cate_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父分类ID',
  `name` varchar(50) NOT NULL COMMENT '分类名称',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '降序排序',
  PRIMARY KEY (`cate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for goods_attribute
-- ----------------------------
DROP TABLE IF EXISTS `goods_attribute`;
CREATE TABLE `goods_attribute` (
  `attr_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '属性ID',
  `goods_id` int(10) unsigned NOT NULL COMMENT '商品ID',
  `name` varchar(50) NOT NULL COMMENT '属性名',
  `value` varchar(50) NOT NULL COMMENT '属性值',
  PRIMARY KEY (`attr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;