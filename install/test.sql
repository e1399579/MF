#测试代码生成器之用
CREATE TABLE mf_test(
id INT unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
`username` varchar(30) NOT NULL COMMENT '用户名',
`last_time` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '最后登录时间',
`user_status` enum('启用','禁用') DEFAULT '启用' COMMENT '用户状态',
hobby set('电影','看书','绘画') NOT NULL DEFAULT '电影' comment '爱好',
content text not null comment '内容',
mood TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '心情：0开心,1惊讶,2平静',
`list_order` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
PRIMARY KEY (id),
KEY (list_order)
)engine=innodb charset=utf8 comment '测试';