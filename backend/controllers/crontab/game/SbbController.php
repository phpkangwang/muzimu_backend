<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/21
 * Time: 15:07
 */

namespace backend\controllers\crontab\game;

use common\models\game\sbb\Sbb;
use Yii;

class SbbController extends GameBaseController
{
    public function __construct($gameName)
    {
        $this->gameName           = $gameName;
        $this->LocusRedisKey      = "locusSbb";
        $this->LocusMaxIdRedisKey = "locusSbbMaxId";

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
        $resetErrTimes = $this->resetErrTimes;//错误多少次后不再放入循环
        $etime         = $time * 1000;
        $datas         = $redis->ZRANGEBYSCORE($redisKey, 0, $etime);
        $newArr        = array();
        foreach ($datas as $key => $val) {
            $datas[$key]                = json_decode($val, true);
            $datas[$key]['create_time'] = isset($datas[$key]['create_time']) ? $datas[$key]['create_time'] : 0;
            $uniqueId                   = $datas[$key]['unique_str'];
            if (isset($newArr[$uniqueId])) {
                //计较两条数据的时间的大小，后面的时间的数据覆盖前面的时间的数据
                if ($newArr[$uniqueId]['create_time'] <= $datas[$key]['create_time']) {
                    foreach ($datas[$key] as $k => $v) {
                        $newArr[$uniqueId][$k] = $v;
                    }
                } else {
                    foreach ($datas[$key] as $k => $v) {
                        if (!isset($newArr[$uniqueId][$k])) {
                            $newArr[$uniqueId][$k] = $v;
                        }
                    }
                }
            } else {
                $newArr[$uniqueId] = $datas[$key];
            }
        }
        //循环数据 取出没有完成的数据，插入redis
        foreach ($newArr as $key => $val) {
            $val['reset'] = isset($val['reset']) ? $val['reset'] : 1;
            //连续循环 ? 次还没有完成就当做已经完成了
            if ($val['reset'] == $resetErrTimes) {
                $val['is_complete'] = 1;
            }

            //再次加入到循环
            if (!isset($val['is_complete']) || $val['is_complete'] != 1) {
                $val['reset']++;
                //因为删除reids的是当前时间所以时间增加一毫秒
                $redis->ZADD($redisKey, $time * 1000 + 1, json_encode($val));
                unset($newArr[$key]);
            }
        }

        //把数据按照创建时间排序
        $columnStr = implode(",", $tableColumns);
        $valuesArr = array();

        //轨迹表当前最大的id
        $startId = $this->getLocusMaxId($this->LocusMaxIdRedisKey);
        $this->myLog(json_encode($newArr));
        foreach ($newArr as $key => $val) {
            $startId++;

            $val['add_buy_play_score']  = isset($val['add_buy_play_score']) ? $val['add_buy_play_score'] : 0;
            $val['add_buy_win_score']   = isset($val['add_buy_win_score']) ? $val['add_buy_win_score'] : 0;
            $val['add_buy_play_number'] = $val['add_buy_play_score'] > 0 ? 1 : 0;
            $val['add_buy_win_number']  = $val['add_buy_win_score'] > 0 ? 1 : 0;
            $val['win_score']           -= $val['add_buy_win_score'];
            $val['win']                 -= $val['add_buy_win_score'];
            $val['bet']                 -= $val['add_buy_play_score'];
            $val['id']                  = $startId;
            $val['compare_id']          = isset($val['compare_id']) ? $val['compare_id'] : 2;//默认没有比备
            $val['create_time']         = isset($val['create_time']) ? (int)($val['create_time'] / 1000) : 0;
            $val['update_time']         = 0;
            $val['game_type']           = $this->gameObj->gameType;
            $values                     = "";
            $arr                        = array();
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
        $this->myLog("\r\n\t执行的游戏是" . $this->gameName . "\r\n\t当前插入数据库一共有：" . count($valuesArr) . " 条");
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