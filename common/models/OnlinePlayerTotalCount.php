<?php

namespace common\models;

use backend\controllers\platform\oversea\backend\models\Tool;
use common\models\game\FivepkAccount;
use common\models\game\FivepkOrder;
use common\models\game\FivepkPoint;
use common\models\game\FivepkShareRecord;
use common\models\game\ReportRemain;
use common\services\TimerService;
use Yii;

/**
 * This is the model class for table "online_player_total_count".
 *
 * @property integer $id
 * @property integer $maxonline
 * @property integer $maxonlinedate
 * @property integer $minonline
 * @property integer $minonlinedate
 * @property integer $total_coin_up
 * @property integer $total_coin_down
 * @property integer $new_player_count
 * @property integer $official_total_play_count
 * @property integer $experience_total_play_count
 * @property integer $created_at
 * @property integer $status
 * @property integer $active_player
 * @property float $profit
 * @property integer $send_score
 */
class OnlinePlayerTotalCount extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'online_player_total_count';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['maxonline', 'maxonlinedate', 'minonline', 'minonlinedate', 'total_coin_up', 'total_coin_down', 'new_player_count', 'official_total_play_count', 'experience_total_play_count', 'created_at', 'status', 'active_player', 'send_score'], 'integer'],
            [['profit'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                          => 'id',
            'maxonline'                   => '在线人数峰值',
            'maxonlinedate'               => '在线人数峰值时间',
            'minonline'                   => '在线人数谷值',
            'minonlinedate'               => '在线人数谷值时间',
            'total_coin_up'               => '总上钻',
            'total_coin_down'             => '总下钻',
            'new_player_count'            => '新增玩家',
            'official_total_play_count'   => '正式场总玩局数',
            'experience_total_play_count' => '体验场总玩局数',
            'created_at'                  => '记录时间',
            'status'                      => '状态',
            'active_player'               => '活跃人数',
            'profit'                      => '盈利',
            'send_score'                  => '总送分',
        ];
    }

    //OverSea 峰值记录
    public function OnlineCount()
    {
        $day  = \Yii::$app->request->get('day');
        $day  = $day == "" ? date('Y-m-d', strtotime('-1 day')) : $day;
        $time = strtotime($day) + 86400;

        $tomorrow_date = date('Y-m-d', $time);//当天结束
        $tomorrow      = strtotime($tomorrow_date);//当天结束
        $today         = $tomorrow - 86400;//当天
        $today_date    = date('Y-m-d', $today);//当天

        $DataGameListInfoObj = new DataGameListInfo();
        $GameInfo            = $DataGameListInfoObj->gameDateInfoSum($day);

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
        $total_coin_down = FivepkPoint::find()->filterWhere(['between', 'operate_time', $today_date, $tomorrow_date])->sum('down_coin');

        $playerNum = ReportRemain::getOneActiveDruForTime($today_date, 'ALL');

        //新增玩家总数
//        $new_player_count = FivepkAccount::find()->filterWhere(['between', 'create_date', $today_date, $tomorrow_date])->count();
        $new_player_count = $playerNum['dru'];

        //活跃人数
        $active_player = $playerNum['active'];

        //总送分
        $send_score = $this->getSumSendScore($today, $tomorrow, $today_date, $tomorrow_date);

        //总充值
        $recharge = $this->getRecharge($today_date, $today_date);

        $online_player_total_count  = new OnlinePlayerTotalCount();
        $OnlinePlayerTotalCountType = new OnlinePlayerTotalCountType();

        $tr = Yii::$app->db->beginTransaction();

        $created_at = $tomorrow - 86400;

        $online_player_total_count::deleteAll(['created_at' => $created_at]);
        $OnlinePlayerTotalCountType::deleteAll(['created_at' => $created_at]);
        $postData = array(
            'maxonline'                   => $maxonline,
            'maxonlinedate'               => $maxonlinedate,
            'minonline'                   => $minonline,
            'minonlinedate'               => $minonlinedate,
            'active_player'               => $active_player,
            'total_coin_up'               => Tool::examineEmpty($total_coin_up, 0),
            'total_coin_down'             => Tool::examineEmpty($total_coin_down, 0),
            'new_player_count'            => $new_player_count,
            'experience_total_play_count' => 0,
            'official_total_play_count'   => $GameInfo['other']['总玩局数'],
            'profit'                      => $GameInfo['other']['盈利'],
            'diamond_count'               => Tool::examineEmpty($GameInfo['other']['红包来袭'], 0),
            'send_score'                  => $send_score,
            'created_at'                  => $created_at,
        );
        $online_player_total_count->add($postData);

        //记录各种充值数据
        $OnlinePlayerTotalCountType1 = clone $OnlinePlayerTotalCountType;
        $OnlinePlayerTotalCountType1->add(array(
            'created_at'         => $created_at,
            'pay_type'           => 2,
            'sum_recharge_money' => $recharge['Google充值'],
        ));
        $OnlinePlayerTotalCountType2 = clone $OnlinePlayerTotalCountType;
        $OnlinePlayerTotalCountType2->add(array(
            'created_at'         => $created_at,
            'pay_type'           => 3,
            'sum_recharge_money' => $recharge['OPPO充值'],
        ));

        $tr->commit();
    }


    /**
     *   获取总送分
     */
    public function getSumSendScore($today, $tomorrow, $today_date, $tomorrow_date)
    {
        //总送分
        $send_score = 0;
        //分享邀请送分
        $num        = FivepkShareRecord::find()->select('sum(bonus) as num')->filterWhere(['between', 'create_time', $today * 1000, $tomorrow * 1000])->asArray()->one();
        $send_score += $num['num'];

        //签到
        $num        = \common\models\activity\sign\BackendSignData::find()->select('sum(num) as num')->filterWhere(['between', 'time', $today * 1000, $tomorrow * 1000])->andWhere(['item_type' => 0])->asArray()->one();
        $send_score += $num['num'];

        //关注
        $num        = \common\models\Attention::find()->select('sum(reward) as num')->filterWhere(['between', 'time', $today_date, $tomorrow_date])->asArray()->one();
        $send_score += $num['num'];

        //排行榜
        $num        = \common\models\activity\rank\RankAwardAccount::find()->select('sum(award_num) as num')->andWhere(['award_type' => 0])->filterWhere(['between', 'create_time', $today * 1000, $tomorrow * 1000])->asArray()->one();
        $send_score += $num['num'];

        return $send_score;
    }

    /**
     *  获取总充值
     */
    public function getRecharge($today_date, $today_date)
    {
        //当天玩家总充值金额
        $where = " fivepk_order.account_id = fivepk_account.account_id and status = 2 and fivepk_account.seoid = 'AA' and pay_time >= '" . $today_date . " 00:00:00' and pay_time < '" . $today_date . " 23:59:59'";

        $FivepkOrderObj  = new FivepkOrder();
        $FivepkOrderObjs = $FivepkOrderObj->statisticSumPayType($where);
        $rs              = array(
            'Google充值' => 0,
            'OPPO充值'   => 0,
        );
        foreach ($FivepkOrderObjs as $value) {
            //1-苹果2-谷歌3-OPPO
            if ($value['pay_type'] == 2) {
                $rs['Google充值'] += $value['reMonSum'];
            } elseif ($value['pay_type'] == 3) {
                $rs['OPPO充值'] += $value['reMonSum'];
            }
        }
        return $rs;
    }
}
