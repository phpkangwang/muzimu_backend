<?php
return [
    'platForm'             => 'thwj',
    'adminEmail'           => 'admin@example.com',
    'hashAlgo'             => "sha256",
    'awaitMaxTime'         => 100, //等待操作最大，单位秒
    'url'                  => 'http://106.14.204.137:4088',  //海外新后台测试服java接口
    'payType'              => 2,  //玩家使用金钱类型 1:钻石 2:分数
    'ipMaxTime'            => 2592000,//86400*30;ip大于一个月就要更新
    'showIpAddress'        => ['country', 'region', 'city', 'isp'],//country国家,area地区名称,region省名称,city市名称isp服务商名称
    'upDiamondTimes'       => 1,//上钻倍数
    'downDiamondTimes'     => 10,//下钻倍数
    'locusSaveDay'         => 30,//轨迹保存的天数
    'activityName'         => "红包来袭",//用户送钻报表统计哪一个奖 //现在有 红包来袭，喜从天降
    'locusLog'             => true, //轨迹的 redis 插入数据库的日志的开关
    'redisCommonDatabase'  => 0,//可以随时删除的redis的库
    'redisForeverDatabase' => 10,//不能删除的redis的库
    'refreshRoomAwardGame' => ['HFH', 'HFHH', 'DZB', 'DBS'],//['HFH','DZB'],//java服务端要求，修改房间奖的时候要调用他们的接口
    'byuLocusTableNum'     => 10, //捕鱼的轨迹有多少个表
    'hasServerGameType'    => [0,13],//拥有自己服务器的游戏

    'itemType' => array(
        '金币' => 0,
        '奖券' => 1,
        '话费' => -1,
    ),


    'level'     => [
        'HFH'  => [
            'TY' => 100,
            'CJ' => 200,
            'ZJ' => 500,
            'GJ' => 1000,
        ],
        'ATT2' => [
            'TY' => 100,
            'CJ' => 200,
            'ZJ' => 500,
            'GJ' => 1000,
        ],
    ],

    'game' => [
        'HFH'  => '火凤凰',
        'HFHH' => 'H5火凤凰',
        'DZB'  => '大字板',
        'ATT2' => 'ATT2',
        'MXJ'  => '明星97',
        'DBS'  => '大白鲨',
        'HQL'  => '火麒麟',
        'SBB'  => '超级大亨',
        'PAM'  => 'PAMAN',
        'BAO'  => '雪豹',
        'GHR'  => '黄金赛马',
        'BYU'  => '欢乐捕鱼',
        'HFHH'  => 'H5火凤凰',
    ],

    'gameType'        => [
        '1'  => '火凤凰',
        '2'  => '大白鲨',
        '3'  => '大字板',
        '4'  => 'ATT2',
        '6'  => '明星97',
        '8'  => '超级大亨',
        '10' => 'PAMAN',
        '11' => '雪豹',
        '12' => '黄金赛马',
        '13' => '欢乐捕鱼',
        '14' => 'H5火凤凰',
    ],

    //送钻报表开启的游戏
    'giveAwardReport' => [
        'ATT2',
        '大字板',
        '火凤凰',
        'H5火凤凰',
        '超级大亨',
        '大白鲨',
    ],

    '火凤凰' => [
        'gameType'               => 1,
        'short'                  => 'HFH',
        'userOddsModel'          => '\common\models\game\firephoenix\FivepkPlayerFirephoenixCardtypeandvalue',
        'userOddsDefault'        => '\common\models\game\firephoenix\FivepkPlayerFirephoenixCardtypeandvalueDefault',
        'bigAward'               => 7,
        'playerInfoSwitchColumn' => 'prefab_jail',
    ],

    'H5火凤凰' => [
        'gameType'               => 14,
        'short'                  => 'HFHH',
        'userOddsModel'          => '\common\models\game\hfhh\FivepkPlayerFirephoenixhCardtypeandvalue',
        'userOddsDefault'        => '\common\models\game\hfhh\FivepkPlayerFirephoenixhCardtypeandvalueDefault',
        'bigAward'               => 7,
        'playerInfoSwitchColumn' => 'prefab_jail_hfhh',
    ],

    '大白鲨' => [
        'gameType'               => 2,
        'short'                  => 'DBS',
        'userOddsModel'          => '\common\models\game\big_shark\FivepkPlayerBigSharkCardtypeandvalue',
        'userOddsDefault'        => '\common\models\game\big_shark\FivepkPlayerBigSharkCardtypeandvalueDefault',
        'bigAward'               => 7,
        'playerInfoSwitchColumn' => 'prefab_jail_big_shark',
    ],

    '大字板'   => [
        'gameType'               => 3,
        'short'                  => 'DZB',
        'userOddsModel'          => '\common\models\game\big_plate\FivepkPlayerBigPlateCardTypeAndValue',
        'userOddsDefault'        => '\common\models\game\big_plate\FivepkPlayerBigPlateCardTypeAndValueDefault',
        'bigAward'               => 7,
        'playerInfoSwitchColumn' => 'prefab_jail_big_plate',
    ],
    '明星97'  => [
        'gameType'               => 6,
        'short'                  => 'MXJ',
        'userOddsModel'          => '\common\models\game\star97\StarCardTypeAndValue',
        'userOddsDefault'        => '\common\models\game\star97\StarCardTypeAndValueDefault',
        'bigAward'               => "",
        'playerInfoSwitchColumn' => 'prefab_jail_star97',
    ],
    'ATT2'  => [
        'gameType'               => 4,
        'short'                  => 'ATT2',
        'userOddsModel'          => '\common\models\game\att2\FivepkPlayerAtt2Cardtypeandvalue',
        'userOddsDefault'        => '\common\models\game\att2\FivepkPlayerAtt2CardtypeandvalueDefault',
        'bigAward'               => 7,
        'playerInfoSwitchColumn' => 'prefab_jail_att',
    ],
    '超级大亨'  => [
        'gameType'               => 8,
        'short'                  => 'SBB',
        'userOddsModel'          => '\common\models\game\sbb\FivepkPlayerSbbCardtypeandvalue',
        'userOddsDefault'        => '\common\models\game\sbb\FivepkPlayerSbbCardtypeandvalueDefault',
        'bigAward'               => 11,
        'playerInfoSwitchColumn' => 'prefab_jail_sbb',
    ],
    'PAMAN' => [
        'gameType'               => 10,
        'short'                  => 'PAM',
        'userOddsModel'          => '\common\models\game\paman\FivepkPlayerPamanWintype',
        'userOddsDefault'        => '\common\models\game\paman\FivepkPlayerPamanWintypeDefault',
        'bigAward'               => 11,
        'playerInfoSwitchColumn' => 'prefab_jail_paman',
    ],

    '雪豹' => [
        'gameType'        => 11,
        'short'           => 'BAO',
        'locus'           => '\common\models\game\snow_leopard\SnowLeopardLocus',
        'compare'         => '',
        'userOddsModel'   => '\common\models\game\snow_leopard\FivepkPlayerSnowLeopardWintype',//玩家几率
        'userOddsDefault' => '\common\models\game\snow_leopard\FivepkPlayerSnowLeopardWintypeDefault',//默认玩家几率
        'bigAward'        => 11,
        'playerInfoSwitchColumn' => 'prefab_jail_snow_leopard',
    ],

    '黄金赛马' => [
        'gameType'        => 12,
        'short'           => 'GHR',
        'bigAward'        => 11,
    ],

    '欢乐捕鱼' => [
        'gameType'        => 13,
        'short'           => 'BYU',
        'bigAward'        => 11,
    ],

    'fire_phoenix' => 1,
    'big_shark'    => 2,
    'big_plate'    => 3,
    'att'          => 4,
    'crown'        => 5,
    'star97'       => 6,
    'fire_unicorn' => 7,
    'super'        => 8,
    'paman'        => 10,
    'xb'           => 11,
    'ghr'          => 12,
    'byu'          => 13,
    'hfhh'         => 14,

    'orderRechargeType' => [
        ''                             => '',
        'NORMAL_RECHARGE_ACTIVITY'     => '正常',
        'FIRST_RECHARGE_ACTIVITY'      => '首充',
        'LIMIT_TIME_RECHARGE_ACTIVITY' => '限时',
        'NEWCOMER_AWARD_ACTIVITY'      => '新人',
        'MONTH_CARD_RECHARGE_ACTIVITY' => '月卡',
    ],

    'controller' => [
        'jitai_huofenghuang' => 'phoenix',
        'big_shark'          => 'big-shark',
        'good_luck'          => 'big-plate',
        'att'                => 'att',
        'crown'              => 'crown',
        'star97'             => 'star97',
        'jitai_huoqilin'     => 'fire-unicorn',
    ],

    'old_player_switch' => [
        1 => 'prefab_jail',
        2 => 'prefab_jail_big_shark',
        3 => 'prefab_jail_big_plate',
        6 => 'prefab_jail_star97',
        7 => 'prefab_jail_fire_unicorn',
    ],

    'status' => [
        -10 => '封禁',
        10  => '正常',
        20  => '冻结',
    ],

    'mailReportType' => [
        1 => '账号问题',
        2 => '充值问题',
        3 => 'BUG反馈',
        4 => '合作咨询',
        5 => '其他问题'
    ],

    'pay_order' => [
        10 => '未收款',
        20 => '已收款',
        30 => '疑问单',
    ],

    'withdraw_order' => [
        10 => '未出款',
        20 => '已出款',
        30 => '疑问单',
    ],

    'maintenance_rightId'    => 40,
    'player_offline_rightId' => 38,

    'old_player_switch_val' => [
        'prefab_jail'              => 1,
        'prefab_jail_big_shark'    => 2,
        'prefab_jail_big_plate'    => 3,
        'prefab_jail_att'          => 4,
        ''                         => 5,
        'prefab_jail_star97'       => 6,
        'prefab_jail_fire_unicorn' => 7,
        'prefab_jail_sbb'          => 8,
        'prefab_jail_paman'        => 10,
    ],

    'orderPayType' => [
        1 => "苹果",
        2 => "谷歌",
        3 => "oppo",
    ],

    'orderPayStatus' => [
        -1 => "支付失败",
        0  => "待支付",
        1  => "支付取消",
        2  => "交易成功",
    ],

    'shareType' => [
        0 => "朋友圈",
        1 => "好友",
        2 => "邀请",
    ],

    'givedGift'    => [
        0 => "未收礼",
        1 => "已收礼",
    ],

    //纪律列表 1新玩家 2机台 3老玩家  ""代表所有
    'oddsTypeList' => [1, 2, 3, ""],
    'noOddsTypeList' => [ 2,  ""],
    'oddsTypeInfo' => ["新玩家" => 1, "机台玩家" => 2, "老玩家" => 3],
    /*
               体验场        初级场        中级场    vip场
    大顺         2000         20000         20000    100000
    五梅         800          8000          8000     40000
    小顺         400          4000          4000     20000
    四梅         10           100            100     500
 */
    'pamanSetGift' => [
        1 => [
            'bonus_rs'    => 2000,
            'bonus_fivek' => 800,
            'bonus_sf'    => 400,
            'bonus_fourk' => 10
        ],
        2 => [
            'bonus_rs'    => 10000,
            'bonus_fivek' => 4000,
            'bonus_sf'    => 2000,
            'bonus_fourk' => 50
        ],
        3 => [
            'bonus_rs'    => 20000,
            'bonus_fivek' => 8000,
            'bonus_sf'    => 4000,
            'bonus_fourk' => 100
        ],
        5 => [
            'bonus_rs'    => 100000,
            'bonus_fivek' => 40000,
            'bonus_sf'    => 20000,
            'bonus_fourk' => 500
        ],
    ],

    /*
           体验场        初级场        中级场         高级场
            50000         100000         248800        518800
            不能大于这些数值
     */

    'star97setGift' => [
        1 => 50000,
        2 => 100000,
        3 => 248800,
        4 => 518800,
        5 => 1088800,
    ],

    //明星97的奖项
    'mxjPrizeItem' =>[
        1 => "连线奖",
        2 => "7奖",
        3 => "连线带7奖",
        4 => "全盘奖",
        5 => "明星奖",
    ],

    //红包来袭统计 新轨迹 backend_prize_hfh_day compare_json里面存的对应的字段
    'diamondTimes'  => [
        1 => 'diamond_one',
        2 => 'diamond_two',
        3 => 'diamond_three',
        4 => 'diamond_four',
        5 => 'diamond_five',
    ],
    //红包来袭统计 新轨迹 backend_prize_hfh_day compare_json里面存的对应的字段
    'diamondCount'  => [
        1 => 'diamond_one_count',
        2 => 'diamond_two_count',
        3 => 'diamond_three_count',
        4 => 'diamond_four_count',
        5 => 'diamond_five_count',
    ],

    'prizeToChinese' => [
        'play_score'           => "总玩分数",
        'win_score'            => "总赢分数",
        'play_number'          => "总玩局数",
        'win_number'           => "总赢局数",
        'compareBetScore'      => "比倍总玩分数",
        'compareWinScore'      => "比倍总赢分数",
        'comparePlayNumber'    => "比倍总玩局数",
        'compareWinNumber'     => "比倍总赢局数",
        'compareGuoguanScore'  => "过关分数",
        'compareGuoguanNumber' => "过关局数",
        'compareBaojiScore'    => "爆机分数",
        'compareBaojiNumber'   => "爆机局数",
        'SumBonusScore'        => "总彩金",//明星97
        'SumDiamondScore'      => "总送钻",//明星97
        'bonusScore'           => "彩金分数",
        'bonusNumber'          => "彩金次数",
        'add_buy_play_score'   => "加买总玩分数",
        'add_buy_win_score'    => "加买总赢分数",
        'add_buy_play_number'  => "加买总玩局数",
        'add_buy_win_number'   => "加买总赢局数",
        'gold_card'            => "黄金牌",
        'platinum_card'        => "白金牌",


    ],
];
