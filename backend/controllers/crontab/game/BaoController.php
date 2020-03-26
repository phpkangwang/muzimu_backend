<?php

namespace backend\controllers\crontab\game;


/**
 *  定时任务工作流程介绍:
 *  每隔一段时间($crontabTime)从redis里面读取轨迹，存到轨迹同时日，周，月表中 (要确保三个表的自增id是一致的)，
 *  每个一段时间统计一次prize表，然后每天0点把前一天的prize数据删除掉再重新统计一遍，可以减少数据量
 *
 * Class HfhController
 * @package backend\controllers\crontab\game
 */
class BaoController extends GameBaseController
{
    public function __construct($gameName)
    {
        $this->gameName           = $gameName;
        $this->LocusRedisKey      = "locusBao";
        $this->LocusMaxIdRedisKey = "locusBaoMaxId";
        parent::__construct();
    }

    /**
     *   定时处理轨迹
     */
    public function Locus($time)
    {
        $tableColumns = $this->gameObj->getModelLocusDay()->attributes();

        //获取表字段
        $redisKey = $this->LocusRedisKey;
        $redis    = $this->myredis;
        //取出定义分钟数的数据
        $etime = $time * 1000;
        $datas = $redis->ZRANGEBYSCORE($redisKey, 0, $etime);

        if (empty($datas)) {
            return '';
        }

        //把数据按照创建时间排序
        $columnStr = implode(",", $tableColumns);
        $valuesArr = array();

        $startId = $this->getLocusMaxId($this->LocusMaxIdRedisKey);

        foreach ($datas as $key => $val) {
            $startId++;
            $val                = json_decode($val, true);
            $val['id']          = $startId;
            $val['create_time'] = isset($val['create_time']) ? (int)($val['create_time'] / 1000) : 0;
            $val['game_type']   = $this->gameObj->gameType;
            $room_index         = explode('11_', $val['room_index']);
            $val['room_index']  = isset($room_index[1]) ? $room_index[1] : 0;
            $val['update_time'] = 0;
            $values             = "";
            $arr                = array();
            //保证数据的顺序
            foreach ($tableColumns as $column) {
                //为了防止缺少字段的数据存入数据库
                if (!isset($val[$column])) {
                    $val[$column] = 0;
                }
                $arr[] = $val[$column];
            }

            if (!empty($arr)) {
                $values      .= " ('" . implode("','", $arr) . "') ";
                $valuesArr[] = $values;
            }
        }

        $this->myLog("\r\n\t执行的游戏是" . $this->gameName ."\r\n\t当前插入数据库一共有：" . count($valuesArr)." 条");
        //有数据的时候才插入
        if (!empty($valuesArr)) {
            $valuesStr = implode(" , ", $valuesArr);
            //插入日，周，月三张表
            $sql = "
                    insert into {$this->gameObj->tableLocusDay} ($columnStr) values {$valuesStr};
                    insert into {$this->gameObj->tableLocusMonth} ($columnStr) values {$valuesStr};
            ";
            //插入数据库
            $this->mydb->createCommand($sql)->query();
            $this->setLocusMaxId($this->LocusMaxIdRedisKey, $startId);
        }

        //删除reids的值
        $redis->ZREMRANGEBYSCORE($redisKey, 0, $etime);
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