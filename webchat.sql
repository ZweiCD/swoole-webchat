/*
Navicat MySQL Data Transfer

Source Server         : VMware localhost
Source Server Version : 50552
Source Host           : 192.168.197.129:3306
Source Database       : webchat

Target Server Type    : MYSQL
Target Server Version : 50552
File Encoding         : 65001

Date: 2017-08-15 15:35:29
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for friend
-- ----------------------------
DROP TABLE IF EXISTS `friend`;
CREATE TABLE `friend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `talk_id` int(11) NOT NULL DEFAULT '-1' COMMENT '对话id',
  `uid1` int(11) NOT NULL DEFAULT '-1' COMMENT '用户1',
  `uid2` int(11) NOT NULL DEFAULT '-1' COMMENT '用户2',
  `create_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for group
-- ----------------------------
DROP TABLE IF EXISTS `group`;
CREATE TABLE `group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(50) NOT NULL DEFAULT '' COMMENT '群名称',
  `group_sign` varchar(200) NOT NULL DEFAULT '' COMMENT '群说明',
  `uid` int(11) NOT NULL DEFAULT '-1' COMMENT '创建者id',
  `create_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for group_member
-- ----------------------------
DROP TABLE IF EXISTS `group_member`;
CREATE TABLE `group_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL DEFAULT '-1' COMMENT '群id',
  `uid` int(11) NOT NULL DEFAULT '-1' COMMENT '用户id',
  `create_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for message
-- ----------------------------
DROP TABLE IF EXISTS `message`;
CREATE TABLE `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `talk_type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '会话类型 1：个人，2：群',
  `talk_id` int(11) NOT NULL DEFAULT '-1' COMMENT '聊天id',
  `uid` int(11) NOT NULL DEFAULT '-1' COMMENT '创建者id',
  `msg_type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '消息类型 1：text，2：image，3：file',
  `msg` text NOT NULL COMMENT '消息',
  `is_read` tinyint(2) NOT NULL DEFAULT '1' COMMENT '是否已读 1：未读，2：已读',
  `create_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for request
-- ----------------------------
DROP TABLE IF EXISTS `request`;
CREATE TABLE `request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_uid` int(11) NOT NULL DEFAULT '-1' COMMENT '发起者id',
  `type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '类型 1：好友邀请 2：群邀请',
  `group_id` int(11) NOT NULL DEFAULT '-1' COMMENT '群id，type为2时需要填写',
  `to_uid` int(11) NOT NULL DEFAULT '-1' COMMENT '被邀请者id',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '是否同意 1：未处理，2：同意，3：拒绝',
  `create_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account` varchar(16) NOT NULL DEFAULT '' COMMENT '账号',
  `password` varchar(100) NOT NULL DEFAULT '' COMMENT '密码',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `create_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for user_info
-- ----------------------------
DROP TABLE IF EXISTS `user_info`;
CREATE TABLE `user_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '-1' COMMENT '用户id',
  `nick_name` varchar(32) NOT NULL DEFAULT '' COMMENT '昵称',
  `sign` varchar(200) NOT NULL DEFAULT '' COMMENT '个人说明',
  `sex` tinyint(2) NOT NULL DEFAULT '1' COMMENT '性别 1：男，2：女',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `create_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
