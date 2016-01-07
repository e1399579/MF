CREATE TABLE `mf_admin` (
  `admin_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `username` varchar(30) NOT NULL COMMENT '用户名',
  `password` char(32) NOT NULL COMMENT '密码',
  `email` varchar(30) NOT NULL DEFAULT '' COMMENT '邮箱',
  `last_ip` char(15) NOT NULL DEFAULT '' COMMENT '最后登录IP',
  `last_time` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '最后登录时间',
  `register_time` datetime NOT NULL COMMENT '注册时间',
  `user_status` enum('启用','禁用') DEFAULT '启用' COMMENT '用户状态',
  `role_id` smallint(5) unsigned NOT NULL COMMENT '角色ID',
  PRIMARY KEY (`admin_id`),
  KEY `username` (`username`),
  KEY `email` (`email`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB CHARSET=utf8 COMMENT='管理员';

CREATE TABLE `mf_menu` (
  `menu_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '菜单ID',
  `parent_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '上级ID',
  `module` char(20) NOT NULL COMMENT '模块',
  `controller` char(20) NOT NULL COMMENT '控制器',
  `action` char(20) NOT NULL COMMENT '方法',
  `is_display` enum('显示','隐藏') NOT NULL DEFAULT '显示' COMMENT '是否显示',
  `name` varchar(50) NOT NULL COMMENT '菜单名称',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT '菜单图标',
  `list_order` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`menu_id`),
  KEY `parent_id` (`parent_id`),
  KEY `controller` (`controller`)
) ENGINE=InnoDB CHARSET=utf8 COMMENT='菜单';

INSERT INTO `mf_menu` VALUES (1,0,'Admin','Menu','index','显示','菜单管理','icon-tasks',0),(2,1,'Admin','Menu','search','隐藏','搜索菜单','',0),(3,1,'Admin','Menu','add','隐藏','添加菜单','',0),(4,1,'Admin','Menu','addPost','隐藏','菜单表单添加提交','',0),(5,1,'Admin','Menu','del','隐藏','删除菜单','',0),(6,1,'Admin','Menu','bdel','隐藏','批量删除菜单','',0),(7,1,'Admin','Menu','save','隐藏','修改菜单','',0),(8,1,'Admin','Menu','savePost','隐藏','菜单表单修改提交','',0),(9,0,'Admin','Role','index','显示','角色管理','icon-user-md',0),(10,9,'Admin','Role','search','隐藏','搜索角色','',0),(11,9,'Admin','Role','add','隐藏','添加角色','',0),(12,9,'Admin','Role','addPost','隐藏','角色表单添加提交','',0),(13,9,'Admin','Role','del','隐藏','删除角色','',0),(14,9,'Admin','Role','bdel','隐藏','批量删除角色','',0),(15,9,'Admin','Role','save','隐藏','修改角色','',0),(16,9,'Admin','Role','savePost','隐藏','角色表单修改提交','',0),(17,0,'Admin','Admin','index','显示','管理员','icon-user',0),(18,17,'Admin','Admin','search','隐藏','搜索管理员','',0),(19,17,'Admin','Admin','add','隐藏','添加管理员','',0),(20,17,'Admin','Admin','addPost','隐藏','管理员表单添加提交','',0),(21,17,'Admin','Admin','del','隐藏','删除管理员','',0),(22,17,'Admin','Admin','bdel','隐藏','批量删除管理员','',0),(23,17,'Admin','Admin','save','隐藏','修改管理员','',0),(24,17,'Admin','Admin','savePost','隐藏','管理员表单修改提交','',0),(25,0,'Admin','Music','index','显示','音乐管理','icon-music',0),(26,25,'Admin','Music','search','隐藏','搜索音乐','',0),(27,25,'Admin','Music','add','隐藏','添加音乐','',0),(28,25,'Admin','Music','addPost','隐藏','音乐表单添加提交','',0),(29,25,'Admin','Music','del','隐藏','删除音乐','',0),(30,25,'Admin','Music','bdel','隐藏','批量删除音乐','',0),(31,25,'Admin','Music','save','隐藏','修改音乐','',0),(32,25,'Admin','Music','savePost','隐藏','音乐表单修改提交','',0);

CREATE TABLE `mf_role` (
  `role_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '角色ID',
  `name` varchar(30) NOT NULL COMMENT '角色名称',
  `menu_id_list` varchar(255) NOT NULL DEFAULT '0',
  `remarks` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `list_order` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`role_id`),
  KEY `name` (`name`),
  KEY `list_order` (`list_order`)
) ENGINE=InnoDB CHARSET=utf8 COMMENT='角色';

INSERT INTO `mf_role` VALUES (1,'超级管理员','*','拥有网站最高管理权限！',now(),0);

CREATE TABLE `mf_music` (
   `music_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '音乐ID',
   `title` varchar(30) NOT NULL COMMENT '标题',
   `artist` varchar(30) NOT NULL COMMENT '艺术家',
   `path` varchar(255) NOT NULL COMMENT '文件路径',
   `album` varchar(30) NOT NULL DEFAULT '' COMMENT '专辑',
   `publish_time` year(4) NOT NULL DEFAULT '1970' COMMENT '发行年份',
   `cover` varchar(255) NOT NULL DEFAULT '' COMMENT '封面',
   `list_order` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
   `music_md5` char(32) NOT NULL DEFAULT '' COMMENT 'md5值',
   `size` int(11) NOT NULL DEFAULT '0' COMMENT '文件大小',
   `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态：1启用,0禁用',
   `update_time` timestamp NOT NULL COMMENT '更新时间',
   PRIMARY KEY (`music_id`),
   KEY `title` (`title`),
   KEY `artist` (`artist`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='音乐';