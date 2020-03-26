<?php

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