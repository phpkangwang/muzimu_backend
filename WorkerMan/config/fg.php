<?php
/**
/timer/online-player    统计在线人数的最大值  每5分钟统计一次
/timer/online-count     峰值记录  每天0点5分钟执行 必须在人气报表后面执行
/timer/hits-report      人气报表  每天0点执行

####  /timer/limited-activity-notice   不再使用
####  /timer/best-bet-notice   不再使用

/timer/get-ip   获取用户最后一次登录的ip   10分钟运行一次
#  /timer/set-experience  旧的送钻报表  不再使用
#  /timer/set-experience-extend  旧的送钻报表  不再使用

/crontab/exchange-record/runt-give-award-report   //执行生成送钻报表   每天0点执行
/crontab/report/player-activity-num     //记录每天活跃人数  每天00:00:00 \WorkerMan\config\common.php
/crontab/activity/red-pack-record-to-before-day  //初始化前一天的送钻报表 每天 00:00:00  WorkerMan\config\thwj.php
/crontab/report/player-game-prize-day            //旧轨迹的游戏移到新轨迹的计算方法  每天 00:00:00  WorkerMan\config\thwj.php
/crontab/report/init-path       //每分钟统计上机轨迹 后台计算盈利    每分钟运行  WorkerMan\config\thwj.php

/crontab/notice/notice-send-to-crontab    //系统公告定时发送   每分钟运行
/crontab/activity/red-pack-notice         //红包来袭活动发送公告   每分钟运行
/crontab/activity/limit-notice	          //限时活动发送公告     每分钟运行

/crontab/report/init-hit-report            //人气报表    每天0点5分钟的时候运行

/crontab/report/fg-record-player-day        //fg游戏 玩家盈利每日统计 每天 0点20分执行
/crontab/report/fg-record-game-day        //fg游戏 游戏盈利每日统计 每天 0点20分执行
 */


return array(

    //轨迹
    array(
        'url' =>  "/crontab/game/game-crontab/transfer-minute",
        'time' => array(
            'hour'   => 0, //几点
            'minute' => 0, //几分
            'second' => 10, //几秒
        ),
        'type' => "second"
    ),

    //每天统计前一天数据总结
    array(
        'url' =>  "/crontab/game/game-crontab/record-to-before-day",
        'time' => array(
            'hour'   => 0, //几点
            'minute' => 30, //几分
            'second' => 0, //几秒
        ),
        'type' => "minute"
    ),


);