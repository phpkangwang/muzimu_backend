<?php

return array(

    //轨迹
    array(
        'url' =>  "/crontab/game/hfh/transfer-minute",
        'time' => array(
            'hour'   => 0, //几点
            'minute' => 0, //几分
            'second' => 10, //几秒
        ),
        'type' => "second"
    ),

    //每天统计前一天数据总结
    array(
        'url' =>  "/crontab/game/hfh/record-to-before-day",
        'time' => array(
            'hour'   => 0, //几点
            'minute' => 1, //几分
            'second' => 0, //几秒
        ),
        'type' => "day"
    ),

    //轨迹
    array(
        'url' =>  "/crontab/game/dzb/transfer-minute",
        'time' => array(
            'hour'   => 0, //几点
            'minute' => 0, //几分
            'second' => 10, //几秒
        ),
        'type' => "second"
    ),

    //每天统计前一天数据总结
    array(
        'url' =>  "/crontab/game/dzb/record-to-before-day",
        'time' => array(
            'hour'   => 0, //几点
            'minute' => 1, //几分
            'second' => 0, //几秒
        ),
        'type' => "day"
    ),
);