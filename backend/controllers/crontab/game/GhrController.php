<?php

namespace backend\controllers\crontab\game;

use common\models\DataGameListInfo;
use common\models\game\ghr\Ghr;
use common\models\game\ghr\GhrRedisLocusHandler;
use Yii;

/**
 *  定时任务工作流程介绍:
 *  每隔一段时间($crontabTime)从redis里面读取轨迹，存到轨迹同时日，周，月表中 (要确保三个表的自增id是一致的)，
 *  每个一段时间统计一次prize表，然后每天0点把前一天的prize数据删除掉再重新统计一遍，可以减少数据量
 *
 * Class HfhController
 * @package backend\controllers\crontab\game
 */
class GhrController extends GameBaseController
{
    public function __construct($gameName)
    {
        $this->gameName           = $gameName;
        $this->LocusRedisKey      = "locusGhr";
        $this->LocusMaxIdRedisKey = "locusGhrMaxId";

        parent::__construct();
    }


    /**
     * 定时处理轨迹
     * @param $time
     * @return mixed|string
     */
    public function Locus($time)
    {
        $redisKey = $this->LocusRedisKey;
        $redis    = $this->myredis;
        //取出定义分钟数的数据
        $etime = $time * 1000;
        $datas = $redis->ZRANGEBYSCORE($redisKey, 1570788160000, $etime);

        //$datas=['{"gameType":12,"random":"1","bestHorseGroup":"3_6","settleTime":"1571107239000","jp":"1","playerScoreAfterMakeSureBetMap":"{\"16\":88597}","profitScore":"77","houseGroupKeyAndRateValueMap":"{\"1_2\":30,\"1_3\":10,\"2_3\":1000,\"1_4\":125,\"1_5\":4,\"2_4\":250,\"1_6\":5,\"3_4\":80,\"2_5\":20,\"2_6\":60,\"3_5\":500,\"3_6\":8,\"4_5\":175,\"4_6\":3,\"5_6\":100}","playerHouseGroupKeyAndBetScoreMap":"{\"16\":{\"1_2\":600,\"1_3\":600,\"2_3\":300,\"1_4\":500,\"1_5\":700,\"2_4\":500,\"1_6\":800,\"3_4\":300,\"2_5\":600,\"2_6\":500,\"3_5\":400,\"3_6\":800,\"4_5\":500,\"4_6\":100,\"5_6\":500}}","roomIndex":"12_2"}'];

        if (empty($datas)) {
            return '';
        }

        //把数据按照创建时间排序
        $columnStr      = 'id,account_id,pop_code,game_type,room_index,credit,bet_sum,bet_json,win,prize_out_id,prize_id,win_prize_id,machine_play_count,update_time,create_time,prize_json,jp,play_num,house_group_rate_json,profit_score,bet_rate_json';
        $columnPrizeStr = 'account_id,machine_auto_id,room_index,win_score,play_score,play_number,win_number,update_time,create_time,profit_score,room_prize_json,player_prize_json,play_sum,player_count';

        $startId = $this->getLocusMaxId($this->LocusMaxIdRedisKey);

        GhrRedisLocusHandler::$time = $time;

        $sqlData = GhrRedisLocusHandler::redisLocusHandler($datas, $startId);

        $userLocusString = '';
        $userPrizeString = '';
        $roomPrizeString = '';

        foreach ($sqlData as $value) {
            if (!empty($value['userLocusData'])) {
                foreach ($value['userLocusData'] as $val) {
                    $userLocusString .= " ('" . implode("','", $val) . "') ,";
                }
            }
            if (!empty($value['userPrizeData'])) {
                foreach ($value['userPrizeData'] as $val) {
                    $userPrizeString .= " ('" . implode("','", $val) . "') ,";
                }
            }
            if (!empty($value['roomPrizeData'])) {
                $roomPrizeString .= " ('" . implode("','", $value['roomPrizeData']) . "') ,";
            }
        }
        $sql = '';
        if (!empty($userLocusString)) {
            $userLocusString = substr($userLocusString, '0', -1);
            $sql             .= "
                    insert into {$this->gameObj->tableLocusDay} ($columnStr) values {$userLocusString};
                    insert into {$this->gameObj->tableLocusMonth} ($columnStr) values {$userLocusString};
            ";
        }

        if (!empty($userPrizeString)) {
            $userPrizeString = substr($userPrizeString, '0', -1);
            $sql             .= "
                    insert into {$this->gameObj->tablePrizeDay} ($columnPrizeStr) values {$userPrizeString};
            ";
        }
        if (!empty($roomPrizeString)) {
            $roomPrizeString = substr($roomPrizeString, '0', -1);
            $sql             .= "
                    insert into {$this->gameObj->tablePrizeDay} ($columnPrizeStr) values {$roomPrizeString};
            ";
        }

        //有数据的时候才插入
        if (!empty($sql)) {
            //插入数据库
            $this->mydb->createCommand($sql)->query();
            $DataGameListInfoObj = new DataGameListInfo();
            if ($DataGameListInfoObj->gameIsOpen()) {
                //抽成
                GhrRedisLocusHandler::profitSum(GhrRedisLocusHandler::$profitScore);
            } else {
                //抽成 关服时记录
                GhrRedisLocusHandler::testProfitSum(GhrRedisLocusHandler::$profitScore);
            }

        }

        $this->setLocusMaxId($this->LocusMaxIdRedisKey, $startId);

        //删除reids的值
        $redis->ZREMRANGEBYSCORE($redisKey, 1570788160000, $etime);

    }


    /**
     * 设置轨迹自增最大的id
     * @param $redisKey
     * @return int
     */
    public function getLocusMaxId($redisKey)
    {
        $redis = $this->myredis;
        $obj   = $redis->get($redisKey);
        if (empty($obj)) {
            $sql = "select id,machine_play_count from {$this->gameObj->tableLocusMonth} order by id desc limit 1";
            $obj = $this->mydb->createCommand($sql)->queryOne();
            if (empty($obj)) {
                $obj = ['id' => 0, 'machine_play_count' => 0];
            }
        } else {
            $obj = json_decode($obj, true);
        }
        return $obj;
    }

    /**
     * 设置轨迹自增最大的id
     * @param $redisKey
     * @param $maxId
     */
    public function setLocusMaxId($redisKey, $maxId)
    {
        $redis = $this->myredis;
        $redis->set($redisKey, json_encode($maxId));
    }


    /**
     *   每分钟迁移一分钟前的数据
     */
    public function TransferMinute($time)
    {
        $this->microStime = microtime();
        //统计轨迹
        $this->Locus($time);
        $this->Prize($time);

        $this->useTimeLog();
        return true;
    }

}