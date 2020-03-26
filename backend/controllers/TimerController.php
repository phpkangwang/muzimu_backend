<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/21
 * Time: 15:07
 */

namespace backend\controllers;

use backend\controllers\crontab\CrontabController;
use backend\models\Factory;
use backend\models\redis\MyRedis;
use backend\models\Tool;
use common\models\DataRoomInfoList;
use common\models\FivepkPrizeType;
use common\models\FivepkRecordFreshWin;
use common\models\game\FivepkOrder;
use common\models\game\GameClear;
use common\models\HitsReport;
use common\models\PushManagement;
use common\models\RecordDiamond;
use common\models\RecordHits;
use common\services\IpArea;
use Yii;
use common\models\DataGameListInfo;
use common\models\game\FivepkPlayerInfo;
use common\models\OnlinePlayerOftenCount;
use common\models\game\FivepkAccount;
use yii\filters\VerbFilter;

class TimerController extends CrontabController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * 记录实时在线人数
     */
    public function actionOnlinePlayer()
    {
        $counts            = FivepkPlayerInfo::find()->filterWhere(['is_online' => 1])->count();
        $model             = new OnlinePlayerOftenCount();
        $model->online     = (int)$counts;
        $model->created_at = time();
        if ($model->validate() && $model->save()) {
            echo 'success';
        }
    }

    /**
     * 统计前一天的峰值记录
     */
    public function actionOnlineCount()
    {
        Factory::TimerController()->OnlineCount();
    }

    /**
     * 统计前一天的峰值记录  纠正每天凌晨执行的错误的数据
     *  正式场总玩局数   体验场总玩局数   盈利
     */
    public function actionOnlineCountCover()
    {
        $day      = Yii::$app->request->get('day');
        $day      = isset($day) ? $day : date("Y-m-d", time() - 86400);
        $红包来袭  = 0;
        $正式场总玩局数  = 0;
        $体验场总玩局数  = 0;
        $新玩家总玩局数  = 0;
        $机台玩家总玩局数 = 0;
        $老玩家总玩局数  = 0;
        $盈利       = 0;
        $新玩家盈利    = 0;
        $机台玩家盈利   = 0;
        $老玩家盈利    = 0;
        //红包来袭
        $红包来袭 = RecordDiamond::find()->filterWhere(['create_time' => $day])->sum('prize_award_num');

        //获取正式场执行的  新的游戏轨迹的人气报表
        $zsSql1 = "select * from backend_record_hits where create_time = '$day' and odds_type <> '' ";
        $data1  = Yii::$app->game_db->createCommand($zsSql1)->queryAll();
        //获取体验场总玩局数
        $DataGameListInfoObj = new DataGameListInfo();
        $体验场总玩局数  = $DataGameListInfoObj->getTyPlayTotalCount($day);

        foreach ($data1 as $val) {
            $profitJson  = json_decode($val['profit_json'], true);
            $playNumJson = json_decode($val['play_num_json'], true);
            if ($val['odds_type'] == Yii::$app->params['oddsTypeInfo']["新玩家"]) {
                $新玩家总玩局数 += $playNumJson['合计'];
                $新玩家盈利   += $profitJson['合计'];
            } else if ($val['odds_type'] == Yii::$app->params['oddsTypeInfo']["机台玩家"]) {
                if ($val['game_type'] == 1 || $val['game_type'] == 3 || $val['game_type'] == 11 || $val['game_type'] == 14) {
                    //这里只看新老玩家的记录  不是新老玩家的不查看
                    $机台玩家总玩局数 += $playNumJson['合计'];
                    $机台玩家盈利   += $profitJson['合计'];
                }
            } else if ($val['odds_type'] == Yii::$app->params['oddsTypeInfo']["老玩家"]) {
                $老玩家总玩局数 += $playNumJson['合计'];
                $老玩家盈利   += $profitJson['合计'];
            }
            $盈利      += $profitJson['合计'];
            $正式场总玩局数 += $playNumJson['合计'];
        }
        $screateTime = strtotime($day . " 00:00:00");
        $ecreateTime = strtotime($day . " 23:59:59");
        $sql         = "update online_player_total_count 
                set official_total_play_count = '{$正式场总玩局数}', 
                newer_total_play_count = '{$新玩家总玩局数}', 
                machine_total_play_count = '{$机台玩家总玩局数}', 
                old_total_play_count = '{$老玩家总玩局数}', 
                    profit = '{$盈利}',
                    newer_profit = '{$新玩家盈利}',
                    machine_profit = '{$机台玩家盈利}',
                    old_profit = '{$老玩家盈利}',
                    diamond_count = '{$红包来袭}',
                    experience_total_play_count = '{$体验场总玩局数}'
                 where created_at >= '{$screateTime}'  and  created_at <= '{$ecreateTime}'
                 ";

        Yii::$app->db->createCommand($sql)->query();
        echo "success";
        die;

    }


    //人气报表
    public function actionHitsReport()
    {
        $day = Yii::$app->request->get('day');
        $day = isset($day) ? $day : date("Y-m-d", time() - 86400);
        //获取所有的游戏
        $DataGameListInfoModel = new DataGameListInfo();
        $openGame              = $DataGameListInfoModel->getOpenGame();
        $RecordHitsModel       = new RecordHits();
        foreach ($openGame as $val) {
            $gameName = $val['shortGame'];
            $RecordHitsModel->RecordToday($day, $gameName);
        }
        return "success";
    }

    /**
     *   历史新人奖列表 每天 00:00:00执行，生成前一天的数据
     */
    public function actionFreshAwardHistory()
    {
        $day = Yii::$app->request->get('day');
        $day = isset($day) ? $day : date("Y-m-d", time() - 86400);
        $FivepkRecordFreshWin = new FivepkRecordFreshWin();
        $FivepkRecordFreshWin->initDay($day);
        return;
    }


    /**
     *   商城兑换系统--兑换记录  5秒执行一次
     */
    public function actionStoreExchangeRecord()
    {
        $redisKey = "storeExchangeRecord";
        $time     = time();
        $redis    = new MyRedis();
        //取出定义分钟数的数据
        $etime = $time * 1000;
        $datas = $redis->ZRANGEBYSCORE($redisKey, 0, $etime);
        if (empty($datas)) {
            return "no data";
        }

        $insertKey = array();
        $insertArr = array();
        foreach ($datas as $key => $val) {
            $val = json_decode($val, true);
            //插入到独立用户id中
            $redis->ZADD($redisKey . "_" . $val['account_id'], $time + $key, $datas[$key]);
            //为了保证字段的顺序
            foreach ($val as $k => $v) {
                $val[$k] = addslashes($v);
                if ($k == "updated_time") {
                    $val["update_time"] = $v / 1000;
                    unset($val['updated_time']);
                }
                if ($k == "created_time") {
                    $val["create_time"] = $v / 1000;
                    unset($val['created_time']);
                }
            }
            $insertKey   = " (" . implode(",", array_keys($val)) . ") ";
            $insertArr[] = " ('" . implode("','", array_values($val)) . "') ";
        }

        $insertStr = implode(" , ", $insertArr);
        $columnStr = $insertKey;
        $sql       = "insert into store_item_exchange_record {$columnStr} values {$insertStr}";
        Yii::$app->game_db->createCommand($sql)->query();

        //删除reids的值
        $redis->ZREMRANGEBYSCORE($redisKey, 0, $etime);
        return true;
    }

    public function actionGetRecharge()
    {
        $models = FivepkOrder::find()->select("
            fivepk_order.account_id as account_id,
            sum(recharge_money) as recharge_money,
            sum(fivepk_order.score) as score,
            sum(gift_score) as gift_score
        ")->filterWhere(['status' => 2])->groupBy('fivepk_order.account_id')->all();
        foreach ($models as $model) {
            FivepkPlayerInfo::updateAll(['total_recharge_money' => $model->recharge_money], ['account_id' => $model->account_id]);
        }
    }

    public function actionGetIp()
    {

        $time     = date('Y-m-d H:i:s', strtotime('-10 minutes'));
        $accounts = FivepkAccount::find()->filterWhere(['between', 'last_login_time', $time, date('Y-m-d H:i:s', time())])->all();
        $IpArea   = new IpArea();
        foreach ($accounts as $key => $account) {
            $IpArea->init($account->account_ip);
            $account->address = $IpArea->serializeData(Yii::$app->params['showIpAddress'], '-');
            $account->save();
        }
        echo 'success';
    }

    /**
     *   报表-留存率  凌晨执行一次
     */
    public function actionRunStatRemain()
    {
        Factory::TimerController()->runStatRemain();
        Factory::TimerController()->runStatRemainChannel();
        echo 'success';
    }

    /**
     *   paman大奖榜  早上6点执行
     */
    public function actionClearPamanBigRank()
    {
        GameClear::clearPamanBigRank();
        echo 'success';
    }

    /**
     *   极光推送定时推送
     */
    public function actionPush()
    {
        $PushManagement = new PushManagement();
        $PushManagement->runPushTiming();
        echo 'success';
    }

    /**
     *   极光推送统计
     */
    public function actionPushReceived()
    {
        $PushManagement = new PushManagement();
        $PushManagement->runFactNumTimer();
        echo 'success';
    }


}