<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/21
 * Time: 15:07
 */

namespace backend\controllers\platform\thwj\backend\controllers;

use backend\controllers\platform\thwj\backend\models\Tool;
use common\models\RecordDiamond;
use Yii;
use backend\models\BaseModel;
use common\models\DataGameListInfo;
use common\models\ExperienceReport;
use common\models\ExperienceReportExtend;
use common\models\game\FivepkAccount;
use common\models\game\FivepkPlayerInfo;
use common\models\game\FivepkPoint;
use common\models\game\FivepkShareRecord;
use common\models\OnlinePlayerOftenCount;
use common\models\OnlinePlayerTotalCount;
use common\services\TimerService;

class TimerController extends BaseModel
{

    /**
     * 引入特质类 主要用到__call
     */
    use \backend\controllers\platform\PlatformTrait;

    /**
     * 统计前一天的峰值记录
     */
    public function OnlineCount()
    {
        $day           = \Yii::$app->request->get('day');
        $day           = $day == "" ? date('Y-m-d', strtotime('-1 day')) : $day;
        $sday          = $day . " 00:00:00";
        $eday          = $day . " 23:59:59";
        $today_date    = $sday;
        $tomorrow_date = $eday;
        $today         = strtotime($sday);
        $tomorrow      = strtotime($eday);

        $models = OnlinePlayerOftenCount::find()->filterWhere(['between', 'created_at', $today, $tomorrow])->orderBy('online DESC')->all();//在线人数记录集合.
        //在线人数峰值
        $maxonline = isset($models[0]) ? $models[0]->online : 0;

        //峰值时间
        $maxonlinedate = isset($models[0]) ? $models[0]->created_at : 0;

        //在线人数谷值
        $minonline = isset($models[count($models) - 1]) ? $models[count($models) - 1]->online : 0;

        //谷值时间
        $minonlinedate = isset($models[count($models) - 1]) ? $models[count($models) - 1]->created_at : 0;

        //总上钻
        $total_coin_up = FivepkPoint::find()->filterWhere(['between', 'operate_time', $today_date, $tomorrow_date])->sum('up_coin');

        //总下钻
        $total_coin_down = FivepkPoint::find()->filterWhere(['between', 'operate_time', $today_date, $tomorrow_date])->andWhere(['in','operator_type',[1,2,4]])->sum('down_coin');

        //新增玩家总数
        $new_player_count = FivepkAccount::find()->filterWhere(['between', 'create_date', $today_date, $tomorrow_date])->count();

        //活跃人数
        $active_player = FivepkAccount::find()->filterWhere(['between', 'last_login_time', $today_date, $tomorrow_date])->count();

        $send_score = FivepkShareRecord::find()->select('sum(bonus) as bonus')->filterWhere(['between', 'create_time', $today * 1000, $tomorrow * 1000])->one();

        $online_player_total_count                              = new OnlinePlayerTotalCount();
        $online_player_total_count->maxonline                   = $maxonline;
        $online_player_total_count->maxonlinedate               = $maxonlinedate;
        $online_player_total_count->minonline                   = $minonline;
        $online_player_total_count->minonlinedate               = $minonlinedate;
        $online_player_total_count->active_player               = $active_player;
        $online_player_total_count->total_coin_up               = empty($total_coin_up) ? 0 : $total_coin_up;
        $online_player_total_count->total_coin_down             = empty($total_coin_down) ? 0 : $total_coin_down;
        $online_player_total_count->new_player_count            = $new_player_count;
        $online_player_total_count->send_score                  = $send_score->bonus;
        $online_player_total_count->created_at                  = $tomorrow;
        if ($online_player_total_count->validate() && $online_player_total_count->save()) {
            echo 'success';
        } else {
            var_dump($online_player_total_count->errors);
        }

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
                if ($val['game_type'] == 1 || $val['game_type'] == 3 || $val['game_type'] == 11) {
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


    /**
     * 获得喜从天降人数并存入数据库
     */
    public function SetExperience()
    {
        $get_diamond_player = FivepkPlayerInfo::find()->filterWhere(['>', 'today_experience_contribution', 0])->count();
        $games              = DataGameListInfo::find()->filterWhere(['>', 'game_number', 0])->andFilterWhere(['game_switch' => 0])->all();
        $starttime          = strtotime(date('Y-m-d') . '-1 minutes') * 1000;
        $endtime            = strtotime(date('Y-m-d') . '+1 day -1 minutes') * 1000;
        foreach ($games as $game) {
            if (in_array($game->game_number, [1, 3])) {
                //$service = \Yii::$app->params[$game->game_name]['service'];
                //$result = $service::GetDiamondPlayer($game->game_number, $starttime, $endtime);
                $model                     = new ExperienceReport();
                $model->get_diamond_player = $get_diamond_player;
                $model->time               = date('Y-m-d', $starttime / 1000);
                $model->game_type          = $game->game_number;
                if ($model->validate() && $model->save()) {
                    echo 'success:' . $game->game_name;
                } else {
                    $str = null;
                    foreach ($model->errors as $error) {
                        $str .= $error[0] . ',';
                    }
                    echo 'error:' . $game->game_name . '|' . $str;
                }
            }
        }

    }

    public function SetExperienceExtend()
    {
        $games = DataGameListInfo::find()->filterWhere(['>', 'game_number', 0])->andFilterWhere(['game_switch' => 0])->all();

        $transaction = \Yii::$app->getDb('db')->beginTransaction();
        $validate    = true;
        foreach ($games as $game) {
            if (in_array($game->game_number, [1, 3])) {
                $model   = ExperienceReport::find()->filterWhere(['game_type' => $game->game_number])->orderBy('id DESC')->one();
                $service = \Yii::$app->params[$game->game_name]['service'];
                $results = $service::GetExperience1();
                foreach ($results as $key => $result) {
                    $model->diamond            += $result['总钻石'];
                    $extend                    = new ExperienceReportExtend();
                    $extend->id_parent         = $model->id;
                    $extend->room_type         = $key;
                    $extend->best_bet1         = $result['机台奖1'];
                    $extend->best_bet2         = $result['机台奖2'];
                    $extend->best_bet3         = $result['机台奖3'];
                    $extend->best_bet4         = $result['房间奖1'];
                    $extend->best_bet5         = $result['房间奖2'];
                    $extend->best_bet_diamond1 = $result['机台奖1钻石'];
                    $extend->best_bet_diamond2 = $result['机台奖2钻石'];
                    $extend->best_bet_diamond3 = $result['机台奖3钻石'];
                    $extend->best_bet_diamond4 = $result['房间奖1钻石'];
                    $extend->best_bet_diamond5 = $result['房间奖2钻石'];
                    $extend->created_at        = date("Y-m-d", strtotime(date('Y-m-d') . '-1 minutes'));
                    $validate                  = $validate && $extend->validate() && $extend->save();
                    if ($validate) {
                        echo $game->game_name . "|" . $key . ":success<br>";
                    } else {
                        $str = null;
                        foreach ($model->errors as $error) {
                            $str .= $error[0] . ',';
                        }
                        echo $game->game_name . "|" . $key . ":" . $str . '<br>';
                    }
                }
                $validate = $validate && $model->save();
            }
        }
        if ($validate) {
            $transaction->commit();
            echo 'SUCCESS<br>';
        } else {
            $transaction->rollBack();
            echo 'ERROR<br>';
        }
    }

    /**
     *   报表-留存率 全部渠道  凌晨执行一次
     */
    public function runStatRemain()
    {
        $StatRemain = new \common\models\game\ReportRemain();
        return $StatRemain->timing('ALL');
    }

}