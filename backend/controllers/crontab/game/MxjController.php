<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/21
 * Time: 15:07
 */

namespace backend\controllers\crontab\game;

use backend\models\Tool;
use common\models\game\star97\Mxj;

class MxjController extends GameBaseController
{
    public function __construct($gameName)
    {
        $this->gameName           = $gameName;
        $this->LocusRedisKey      = "locusMxj";
        $this->LocusMaxIdRedisKey = "locusMxjMaxId";

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

        //把数据按照创建时间排序
        $columnStr = implode(",", $tableColumns);
        $valuesArr = array();

        $startId = $this->getLocusMaxId($this->LocusMaxIdRedisKey);

        foreach ($datas as $key => $val) {
            $startId++;
            $val                      = json_decode($val, true);
            $val['id']                = $startId;
            $val['prize_append_json'] = json_encode($this->getPrizeAppendJson($val));
            $val['create_time']       = isset($val['create_time']) ? (int)($val['create_time'] / 1000) : 0;
            $val['update_time']       = 0;
            $values                   = "";
            $arr                      = array();
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

    /**
     *   明星97中的奖有附加奖
     */
    private function getPrizeAppendJson($val)
    {
        $line = $val['line'];
        $linePrize = array(
            -1 => "aline_one_cherry",        //单樱桃线
            -2 => "aline_two_cherry",        //双樱桃线
            1  => "aline_three_cherry",      //三樱桃线
            2  => "aline_three_orange",      //三橘子线
            3  => "aline_three_mango",       //三芒果线
            4  => "aline_three_watermelon",  //三西瓜线
            5  => "aline_three_bell",        //三铃铛线
            6  => "aline_three_red_bar",     //三红BAR线
            7  => "aline_three_yellow_bar",  //三黄BAR线
            8  => "aline_three_blue_bar",    //三蓝BAR线
            9  => "aline_three_seven",       //三个七线
            10 => "aline_three_any_bar",     //三任意BAR
        );
        $starPrize = array(
            2 => "star_two",    //两倍明星奖
            3 => "star_three",  //三倍明星奖
            4 => "star_four",   //四倍明星奖
        );

        $rs        = array();
        //明星奖
        if( isset($starPrize[$val['star_time']]) ){
            $rs[$starPrize[$val['star_time']]] = 1;
        }

        //附加奖
        $arr       = explode(",", $line);

        foreach ($arr as $val) {
            $val = trim($val);
            if( isset($linePrize[$val]) ){
                Tool::issetInitValue( $rs[$linePrize[$val]], 0);
                $rs[$linePrize[$val]]++;
            }
        }
        return $rs;
    }

}