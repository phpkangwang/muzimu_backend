######2018-12-6   dxylonline   添加服务器开关时间
ALTER TABLE data_game_list_info add last_close_time int(11) DEFAULT 0 NOT NULL COMMENT '最后关闭服务器时间' AFTER game_version_id;
ALTER TABLE data_game_list_info add last_open_time int(11) DEFAULT 0 NOT NULL COMMENT '最后关闭服务器时间' AFTER last_close_time;

ALTER TABLE fivepk_seo_att2 add operator varchar(50) DEFAULT "" NOT NULL COMMENT '操作人' ;
ALTER TABLE fivepk_seo_big_plate add operator varchar(50) DEFAULT "" NOT NULL COMMENT '操作人' ;
ALTER TABLE fivepk_seo_bigshark add operator varchar(50) DEFAULT "" NOT NULL COMMENT '操作人' ;
ALTER TABLE fivepk_seo_firephoenix add operator varchar(50) DEFAULT "" NOT NULL COMMENT '操作人' ;
ALTER TABLE fivepk_seo_fireunicorn add operator varchar(50) DEFAULT "" NOT NULL COMMENT '操作人' ;
ALTER TABLE fivepk_seo_gold_crown add operator varchar(50) DEFAULT "" NOT NULL COMMENT '操作人' ;
ALTER TABLE machine_list_star97 add operator varchar(50) DEFAULT "" NOT NULL COMMENT '操作人' ;

ALTER TABLE admin_rbac_function_list add game varchar(50) DEFAULT "" NOT NULL COMMENT '游戏名称' AFTER url ;

//把乌龙的状态修改为0,页面不显示乌龙
update data_prize_type set status=0 where prize_name="乌龙"

ALTER TABLE `admin_rbac_function_list` ADD COLUMN `level`  int(2) NOT NULL DEFAULT 1 COMMENT '排序' AFTER `type`;

#2019-2-26
ALTER TABLE `data_prize_type`
ADD COLUMN `operator`  varchar(50) NOT NULL DEFAULT '' COMMENT '操作人' AFTER `big_award`;
ADD COLUMN `updated_at`  int(11) NOT NULL DEFAULT 0 COMMENT '修改时间' AFTER `operator`;
ADD COLUMN `created_at`  int(11) NOT NULL DEFAULT 0 COMMENT '创建时间' AFTER `big_award`;

2019-3-1 峰值记录充值总金额
ALTER TABLE `online_player_total_count`
ADD COLUMN `sum_recharge_money`  float(10,2) NOT NULL DEFAULT 0.00 COMMENT '当日充值总金额' AFTER `send_score`;

2019-3-8 fivepk_order 添加索引
ALTER TABLE `fivepk_order`
ADD INDEX `pay_time` (`pay_time`) USING BTREE ;

ALTER TABLE `admin_rbac_role`
CHANGE COLUMN `look_parent_diamond` `look_parent`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否可以查看父类的记录1不能 2可以' AFTER `use_parent_diamond`;

2019-3-12 修改字数限制
ALTER TABLE `data_dictionary_configuration_details`
MODIFY COLUMN `discription`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '描述' AFTER `value_name`;


ALTER TABLE `fivepk_default_odds_firephoenix`
ADD COLUMN `ty_machine_play_count`  bigint(20) NOT NULL DEFAULT 0 COMMENT '红白来袭总玩局数' AFTER `prefab_compare_seven_joker`,
ADD COLUMN `machine_diamond_random_value`  bigint(11) NOT NULL DEFAULT 0 COMMENT '机台红包来袭随机局数' AFTER `ty_machine_play_count`;



ALTER TABLE `fivepk_default_odds_big_plate`
ADD COLUMN `ty_machine_play_count`  bigint(20) NOT NULL DEFAULT 0 COMMENT '红白来袭总玩局数' AFTER `prefab_five_of_a_kind_double`,
ADD COLUMN `machine_diamond_random_value`  bigint(11) NOT NULL DEFAULT 0 COMMENT '机台红包来袭随机局数' AFTER `ty_machine_play_count`;


2019-4-09 明星97表增加last_time
ALTER TABLE `fivepk_default_star97`
ADD COLUMN `last_time`  bigint(20) NULL DEFAULT 0 AFTER `win_diamond`,
ADD INDEX (`last_time`) USING BTREE ;

修改轨迹里面无效索引
ALTER TABLE `fivepk_path`
DROP INDEX `idx_name` ,
ADD INDEX `idx_leave_time` (`leave_time`) USING BTREE ;

公告不限字符长度
ALTER TABLE `fivepk_notice`
MODIFY COLUMN `notice`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `id`;



