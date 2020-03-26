<?php

return array(
    //兑换记录 从redis里面读取兑换记录
    array(
        'url' =>  "/timer/store-exchange-record",
        'time' => array(
            'hour'   => 0, //几点
            'minute' => 0, //几分
            'second' => 60, //几秒
        ),
        'type' => "second"
    ),

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
            'minute' => 1, //几分
            'second' => 0, //几秒
        ),
        'type' => "day"
    ),
);