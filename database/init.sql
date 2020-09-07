CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `nickname` varchar(40) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '昵称',
  `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
  `location` varchar(30) NOT NULL DEFAULT '' COMMENT '地区',
  `wechat_id` varchar(255) NOT NULL DEFAULT '' COMMENT '微信ID',
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否可用',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_wechat_id` (`wechat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户表';

CREATE TABLE `team` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '队伍名',
  `en_name` varchar(60) NOT NULL DEFAULT '' COMMENT '队伍英文名',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '队标',
  `enabled` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否可用',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='队伍表';

CREATE TABLE `schedule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `home_team_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '主队ID',
  `away_team_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客队ID',
  `game_time` timestamp NULL DEFAULT NULL COMMENT '比赛时间',
  `game_result` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '比赛结果',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_game_time` (`game_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='赛程表';

CREATE TABLE `contest` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `schedule_id` int(10) NOT NULL DEFAULT '0' COMMENT '赛程ID',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `bet` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '竞猜下注',
  `success` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '竞猜结果',
  `lucky` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否中奖',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_schedule_user` (`schedule_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='竞猜表';