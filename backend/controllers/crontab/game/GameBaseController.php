<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/21
 * Time: 15:07
 */

namespace backend\controllers\crontab\game;

use backend\models\BaseModel;
use backend\models\redis\MyRedis;
use backend\models\Tool;
use common\models\DataGameListInfo;
use common\models\game\base\GameBase;
use Yii;

/**
 *  定时任务工作流程介绍:
 *  每隔一段时间($crontabTime)从redis里面读取轨迹，存到轨迹同时日，周，月表中 (要确保三个表的自增id是一致的)，
 *  每个一段时间统计一次prize表，然后每天0点把前一天的prize数据删除掉再重新统计一遍，可以减少数据量
 *
 * Class HfhController
 * @package backend\controllers\crontab\game
 */
abstract class GameBaseController extends BaseModel
{
    public $resetErrTimes = 600;
    public $crontabTime = 10;//单位秒
    public $mydb;       //当前表的数据库
    public $myredis;

    public $gameName;

    public $gameObj;

    public $LocusRedisKey;
    public $LocusMaxIdRedisKey;
    public $CompareRedisKey;

    public $Tool;

    public $microStime;
    public $fileLog = "crontab.log";

    public function __construct()
    {
        parent::__construct();
        $this->Tool    = new Tool();
        $this->mydb    = Yii::$app->game_db;
        $this->myredis = new MyRedis();

        $GameBaseObj   = new GameBase();
        $this->gameObj = $GameBaseObj->initGameObj($this->gameName);
    }

    public function useTimeLog()
    {
        $useTime = $this->Tool->microtimeToStr($this->microStime, microtime());
        $content = "\r\n\t执行" . Yii::$app->requestedRoute .
            "\r\n\t执行的游戏是" . $this->gameName .
            "\r\n\t耗时" . $useTime . "\r\n";
        Tool::myLog($content, $this->fileLog);
    }

    public function myLog($content)
    {
        Tool::myLog($content, $this->fileLog);
    }

    /**
     * 定时处理轨迹
     * @param $time
     * @return mixed
     */
    public abstract function Locus($time);


    /**
     * 设置轨迹自增最大的id
     * @param $redisKey
     * @param $table
     * @return int
     */
    public function getLocusMaxId($redisKey)
    {
        $redis = $this->myredis;
        $maxId = $redis->get($redisKey);
        if (empty($maxId)) {
            $sql = "select id from {$this->gameObj->tableLocusMonth} order by id desc limit 1";
            $obj = $this->mydb->createCommand($sql)->queryOne();
            if (!empty($obj)) {
                $maxId = $obj['id'];
            } else {
                $maxId = 0;
            }
        }
        return (int)$maxId;
    }

    /**
     * 设置轨迹自增最大的id
     * @param $redisKey
     * @param $maxId
     */
    public function setLocusMaxId($redisKey, $maxId)
    {
        $redis = $this->myredis;
        $redis->set($redisKey, $maxId);
    }

    /**
     * 每天定时删除locus无用数据
     * @param $day
     */
    public function deleteLocusDay($day)
    {
        $time   = strtotime($day);
        $sqlDay = "
           delete from {$this->gameObj->tableLocusDay} where create_time < {$time};
        ";
        $this->mydb->createCommand($sqlDay)->query();
    }

    /**
     * 删除某一天的prize表
     * @param $day
     * @param $table
     */
    public function deletePrizeDay($day, $table)
    {
        $sql = "delete from {$table} where create_time = '{$day}'";
        $this->mydb->createCommand($sql)->query();
    }

    /**
     *   大奖的统计
     */
    public function Prize($time)
    {
        $param = array(
            'stime' => $time,
            'etime' => $time,
            'type'  => "locus",
        );
        /*插入数据*/
        $this->gameObj->getModelPrizeDay()->LocusToPrize($param);
        $day = date("Y-m-d", $time);
        /*合并 并更新*/
        $this->MergePrizeDay($day);
    }

    /**
     * prizeday 表的一个用户多条数据合并成一条数据
     * @param $day
     */
    public function MergePrizeDay($day)
    {
        //游戏开启的时候直接合并，游戏关闭的时候不合并

        /*维护的时候游戏是关闭的 开服之前会清理数据所以prize表关闭合并*/

        $DataGameListInfoObj = new DataGameListInfo();
        if ($DataGameListInfoObj->gameIsOpen()) {
            $this->gameObj->getModelPrizeDay()->mergePrizeDay($day);
        }
    }


    public function LocusInfo()
    {
        $redisKey = $this->LocusRedisKey;
        $redis    = $this->myredis;
        $datas    = $redis->ZRANGE($redisKey, 0, -1);
        print_r($datas);
        die;
    }

    /**
     *   每天执行前一天的数据  第二天删除前一天的数据
     */
    public function RecordToBeforeDay($day)
    {
        $this->microStime = microtime();
        //删除前一天的轨迹 日表
        $this->deleteLocusDay($day);
        $this->useTimeLog();
        echo $this->gameName.":".$day . ": success</br>";
    }


    /**
     * 统计某一天的prizeday表
     * @param $day
     */
    public function prizeDay($day)
    {
        set_time_limit(3600);  //设置超时时间
        $this->deletePrizeDay($day, $this->gameObj->tablePrizeDay);
        $time = strtotime($day);
        //由于数据量大，把一天的数据拆分成24份，一分一分的插入数据库
        for ($i = 0; $i < 24; $i++) {
            $stime = $time + $i * 3600;
            $etime = $stime + 3600;
            $param = array(
                'stime' => $stime,
                'etime' => $etime,
                'day'   => $day,
                'type'  => 'path',
            );
            //因为是统计一天的数据，所以删除数据也是删除一天的数据
            $this->gameObj->getModelPrizeDay()->LocusToPrize($param);
        }
        echo "success";
    }


    /**
     *   定时处理比备数据
     */
    public function Compare($time)
    {
        //获取表字段
        $tableColumns = $this->gameObj->getModelCompare()->attributes();
        $redisKey     = $this->CompareRedisKey;
        $redis        = $this->myredis;
        //取出定义分钟数的数据
        $resetErrTimes = $this->resetErrTimes;//错误多少次后不再放入循环
        $etime         = $time * 1000;
        $dataJson      = $redis->ZRANGEBYSCORE($redisKey, 0, $etime);
        //把json转换成数组
        $dataArr = array();
        foreach ($dataJson as $key => $val) {
            $dataArr[$key] = json_decode($val, true);
        }
        //查找所有比备的 locus_id
        $locusIdArr   = array_column($dataArr, 'unique_str');
        $model        = $this->gameObj->getModelLocusDay();
        $locusObjs    = $model::find()->where(['unique_str' => $locusIdArr])->asArray()->all();
        $newLocusObjs = array();
        foreach ($locusObjs as $val) {
            $newLocusObjs[$val['unique_str']] = $val['id'];
        }
        //没有轨迹的比备从新插入redis等待下一次循环
        foreach ($dataArr as $key => $val) {
            if (!isset($newLocusObjs[$val['unique_str']])) {
                $val['reset'] = isset($val['reset']) ? $val['reset'] : 1;
                //连续循环10次还没有 抛弃这一条比备
                if ($val['reset'] < $resetErrTimes) {
                    //再次加入到循环
                    $val['reset']++;
                    //因为删除reids的是当前时间所以时间增加一毫秒
                    $redis->ZADD($redisKey, $time * 1000 + 1, json_encode($val));
                }
                unset($dataArr[$key]);
            } else {
                $dataArr[$key]['locus_id'] = $newLocusObjs[$val['unique_str']];
                unset($dataArr[$key]['unique_str']);
            }
        }
        //把数据按照创建时间排序
        $columnStr = implode(",", $tableColumns);
        $valuesArr = array();
        foreach ($dataArr as $val) {
            $values = "";
            $arr    = array();
            //保证数据的顺序
            foreach ($tableColumns as $column) {
                //为了防止缺少字段的数据存入数据库
                if (!isset($val[$column])) {
                    $val[$column] = "";
                }
                if ($column == "create_time") {
                    $arr[] = $val[$column] / 1000;
                } else {
                    $arr[] = $val[$column];
                }
            }

            if (!empty($arr)) {
                $values      .= " ('" . implode("','", $arr) . "') ";
                $valuesArr[] = $values;
            }
        }

        //有数据的时候才插入
        if (!empty($valuesArr)) {
            $valuesStr = implode(" , ", $valuesArr);
            $sql       = "insert into {$this->gameObj->tableCompare} ($columnStr) values {$valuesStr}";
            //插入数据库
            $this->mydb->createCommand($sql)->query();
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
        $this->Compare($time);
        $this->Prize($time);

        $this->useTimeLog();
        return true;
    }
}