<?php

namespace common\models\game\ghr;

use backend\models\redis\MyRedis;
use backend\models\Tool;
use common\models\BackendGlobalConfig;
use common\models\FivepkPrizeOut;
use common\models\FivepkPrizeType;
use common\models\game\FivepkAccount;

class GhrRedisLocusHandler
{
    const GAME_TYPE = 12;

    private static $prizeOutIds;

    public static $time;

    public static $profitScore = 0;//抽成分数

    //处理redis返回信息
    public static function &redisLocusHandler(&$value, &$startId)
    {
        $allArr = [];

        foreach ($value as $key => $val) {
            $allArr[$key] = self::handlerOne($val, $startId);
        }
//        varDump($valuesStr);
//        $valuesStr = rtrim($valuesStr, ',');
        return $allArr;
    }

    public static function handlerOne(&$val, &$startId)
    {

        //每次进入玩家局数+1

        $startId['machine_play_count']++;

        $arr       = json_decode($val, true);
        $jpPrizeId = 0;

        /*
         * bestHorseGroup 本局冠亚军
         * jp jp奖 1否 大于1 都是
         * playerScoreAfterMakeSureBetMap 玩家押注后分数
         * profitScore 抽成分数
         * houseGroupKeyAndRateValueMap 冠亚军马组合倍率
         * playerHouseGroupKeyAndBetScoreMap 玩家押注冠亚军马分数
         * roomIndex 房间
         * random 奖型
         * 得分=玩家最佳组合押注分 * 组合对应倍率 * 组合JP倍率
         * */
        $houseGroupKeyAndRateValueMap      = json_decode($arr['houseGroupKeyAndRateValueMap'], true);//冠亚军马组合倍率 (奖)
        $playerHouseGroupKeyAndBetScoreMap = isset($arr['playerHouseGroupKeyAndBetScoreMap']) ? json_decode($arr['playerHouseGroupKeyAndBetScoreMap'], true) : [];//玩家押注
        $playerScoreAfterMakeSureBetMap    = isset($arr['playerScoreAfterMakeSureBetMap']) ? json_decode($arr['playerScoreAfterMakeSureBetMap'], true) : [];//玩家分数

        $room_index = explode(self::GAME_TYPE . '_', $arr['roomIndex']);
        $room_index = isset($room_index[1]) ? $room_index[1] : 0;

        $sumArr = [
            'win_score'   => 0,
            'play_score'  => 0,
            'play_number' => 0,
            'win_number'  => 0,
            'jp_number'   => 0,
        ];

        //房间开出来的奖
        $houseGroupPrizeIds = [self::getPrizeId($houseGroupKeyAndRateValueMap[$arr['bestHorseGroup']]) => 1];

        if ($arr['jp'] > 1) {
            $sumArr['jp_number']            = 1;
            $jpPrizeId                      = self::getPrizeIdForJp($arr['jp']);
            $houseGroupPrizeIds[$jpPrizeId] = 1;
        }

        $userLocusData = [];
        $userPrizeData = [];
        $roomPrizeData = [];

        foreach ($playerHouseGroupKeyAndBetScoreMap as $userId => $value) {
            $startId['id']++;
            $userInfo = self::getUserInfo($userId);
            $win      = 0;
            if ($value[$arr['bestHorseGroup']] > 0) {
                $win = $value[$arr['bestHorseGroup']] * $houseGroupKeyAndRateValueMap[$arr['bestHorseGroup']];
                if ($arr['jp'] > 1) {
                    $win *= $arr['jp'];
                }
                $sumArr['win_number'] += 1;
                $sumArr['win_score']  += $win;

            }

            $play_score = array_sum($value);

            $sumArr['play_score'] += $play_score;

            $prizeId = $value[$arr['bestHorseGroup']] > 0 ? self::getPrizeId($houseGroupKeyAndRateValueMap[$arr['bestHorseGroup']]) : 0;

            $betRate = [];
            foreach ($value as $houseGroup => $betNum) {
                if ($betNum < 0) {
                    continue;
                }
                $betRate[$houseGroupKeyAndRateValueMap[$houseGroup]] = $betNum;
            }

            ksort($betRate);//升序排序

            //轨迹表
            $userLocusData[$userId] = [
                'id'                    => $startId['id'],
                'account_id'            => $userId,
                'pop_code'              => "{$userInfo['seoid']}",
                'game_type'             => self::GAME_TYPE,
                'room_index'            => $room_index,
                'credit'                => isset($playerScoreAfterMakeSureBetMap[$userId]) ? $playerScoreAfterMakeSureBetMap[$userId] : 0,
                'bet_sum'               => $play_score,
                'bet_json'              => json_encode($playerHouseGroupKeyAndBetScoreMap[$userId]),
                'win'                   => $win,
                'prize_out_id'          => $arr['random'],
                'prize_id'              => $prizeId,
                'win_prize_id'          => self::getPrizeId($houseGroupKeyAndRateValueMap[$arr['bestHorseGroup']]),//当前局数开奖id
                'machine_play_count'    => $startId['machine_play_count'],
                'update_time'           => 0,
                'create_time'           => $arr['settleTime'] / 1000,
                'prize_json'            => json_encode([$arr['bestHorseGroup'] => (Tool::examineEmpty($houseGroupKeyAndRateValueMap[$arr['bestHorseGroup']], 0))]),
                'jp'                    => $arr['jp'],
                'play_num'              => $startId['machine_play_count'],
                'house_group_rate_json' => $arr['houseGroupKeyAndRateValueMap'],//组合倍率 tips：这个值只当做记录
                'profit_score'          => $arr['profitScore'],//抽水 tips：这个值只当做记录
                'bet_rate_json'         => json_encode($betRate),//押注倍率
            ];

            $prizeArr = [$prizeId => 1];
            self::userPrizes($prizeId);
            if ($jpPrizeId > 0) {
                $prizeArr[$jpPrizeId] = 1;
                self::userPrizes($jpPrizeId);
            }

            $sumArr['play_number'] += 1;

            //奖型统计表
            $userPrizeData[$userId] = [
                'account_id'        => $userId,
                'machine_auto_id'   => 0,
                'room_index'        => $room_index,
                'win_score'         => $win,
                'play_score'        => $play_score,
                'play_number'       => 1,
                'win_number'        => $win > 0 ? 1 : 0,
                'update_time'       => self::$time,
                'create_time'       => date('Y-m-d', self::$time),
                'profit_score'      => 0,
                'room_prize_json'   => json_encode($houseGroupPrizeIds),//
                'player_prize_json' => json_encode($prizeArr),
                'play_sum'          => $startId['machine_play_count'],
                'player_count'      => 0,
            ];

        }

        //奖型统计表 由于没有玩家玩也要记录所以 在这里就要将数据写入数据库

        if ($playerHouseGroupKeyAndBetScoreMap) {
            $roomPrizeData = [
                'account_id'        => '0',
                'machine_auto_id'   => 1,
                'room_index'        => $room_index,
                'win_score'         => $sumArr['win_score'],
                'play_score'        => $sumArr['play_score'],
                'play_number'       => $sumArr['play_number'],
                'win_number'        => $sumArr['win_number'],
                'update_time'       => self::$time,
                'create_time'       => date('Y-m-d', self::$time),
                'profit_score'      => $arr['profitScore'],
                'room_prize_json'   => json_encode($houseGroupPrizeIds),//奖
                'player_prize_json' => json_encode(self::$userPrizes),//玩家押中奖
                'play_sum'          => $startId['machine_play_count'],
                'player_count'      => count($playerHouseGroupKeyAndBetScoreMap),//玩家人数
            ];
        }

        self::$profitScore += $arr['profitScore'];//抽水

        return ['userLocusData' => $userLocusData, 'userPrizeData' => $userPrizeData, 'roomPrizeData' => $roomPrizeData];
    }

    public static $userPrizes = [];

    //全部用户获奖总和
    public static function userPrizes($prizeId)
    {
        //用户获奖

        if (isset(self::$userPrizes[$prizeId])) {
            self::$userPrizes[$prizeId] += 1;
        } else {
            self::$userPrizes[$prizeId] = 1;
        }

    }

    //获取用户信息
    public static function getUserInfo(&$userId)
    {
        $data = FivepkAccount::getUserInfoForUserId($userId);
        return $data;
    }

    //获取出奖方式
    public static function getPrizeOutId(&$type)
    {
        if (self::$prizeOutIds) {
            return self::$prizeOutIds[$type];
        }
        $FivepkPrizeOut    = new FivepkPrizeOut();
        $data              = $FivepkPrizeOut->findByGameType(self::GAME_TYPE);
        self::$prizeOutIds = array_column($data, 'id', 'value');
        return self::$prizeOutIds[$type];
    }

    //获取奖型
    public static function getPrizeId(&$rate)
    {
        $prizeArrIndexByPrizeType = FivepkPrizeType::findByGameTypeIndex(self::GAME_TYPE, 'prize_type');
        return $prizeArrIndexByPrizeType[$rate]['id'];
    }

    //获取jp奖型
    public static function getPrizeIdForJp(&$rate)
    {
        $prizeArrIndexByPrizeType = FivepkPrizeType::findByGameTypeIndex(self::GAME_TYPE, 'prize_type', true);
        return $prizeArrIndexByPrizeType[$rate]['id'];
    }

    //获取奖型
    public static function getPrizeIdArr(&$rates)
    {
        if (empty($rates)) {
            return [];
        }
        $data                     = [];
        $prizeArrIndexByPrizeType = FivepkPrizeType::findByGameTypeIndex(self::GAME_TYPE, 'prize_type');
        foreach ($rates as $key => $val) {
            $data[$prizeArrIndexByPrizeType[$val]['id']] = 1;
        }
        return $data;
    }

    //抽水累积
    public static function profitSum($num)
    {
        $type = BackendGlobalConfig::PROFIT_SUM;
        self::ProfitSumData($num, $type);
    }

    //关服测试时用的抽水累积值
    public static function testProfitSum($num)
    {
        //关服测试的时候用这个 房间展示数据是两个相加
        $type = BackendGlobalConfig::TEST_PROFIT_SUM;
        self::ProfitSumData($num, $type);
    }

    public static function ProfitSumData($num, $type)
    {
        if ($num == 0) {
            return true;
        }
        if ($num > 0) {
            $math = '+';
        } else {
            $math = '-';
        }
        $sql = "UPDATE backend_global_config SET value =value{$math}{$num} WHERE type={$type};";
        BackendGlobalConfig::getDb()->createCommand($sql)->query();
    }


    //获取全部抽水累计值
    public static function getAllProfitSum()
    {
        $num = BackendGlobalConfig::find()->where(['in', 'type', [BackendGlobalConfig::PROFIT_SUM, BackendGlobalConfig::TEST_PROFIT_SUM]])->sum('value');
        return $num;
    }

    //清除关服测试的抽水累计值
    public static function cleanTestProfitSum()
    {
        $type = BackendGlobalConfig::TEST_PROFIT_SUM;
        $sql  = "UPDATE backend_global_config SET value =0 WHERE type={$type};";
        BackendGlobalConfig::getDb()->createCommand($sql)->query();
    }


}
