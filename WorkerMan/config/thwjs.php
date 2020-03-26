<?php

return array(
    //红包来袭活动发送公告
    array(
        'url' =>  "/crontab/activity/red-pack-notice",
        'time' => array(
            'hour'   => 0, //几点
            'minute' => 1, //几分
            'second' => 0, //几秒
        ),
        'type' => "minute"
    ),

    //限时活动发送公告
    array(
        'url' =>  "/crontab/activity/limit-notice",
        'time' => array(
            'hour'   => 0, //几点
            'minute' => 1, //几分
            'second' => 0, //几秒
        ),
        'type' => "minute"
    )
);