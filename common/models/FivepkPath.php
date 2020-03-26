<?php

namespace common\models;

use common\models\game\FivepkAccount;
use common\services\ToolService;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "fivepk_path".
 *
 * @property string $id
 * @property string $account_id
 * @property string $nick_name
 * @property integer $game_type
 * @property string $machine_auto_id
 * @property string $seo_machine_id
 * @property integer $fivepk_table_name_id
 * @property integer $enter_score
 * @property integer $leave_score
 * @property integer $enter_year
 * @property integer $enter_month
 * @property integer $enter_day
 * @property string $enter_time
 * @property string $leave_time
 * @property integer $reservation_cost
 * @property string $login_ip
 */
class FivepkPath extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_path';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('game_db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account_id', 'game_type', 'machine_auto_id', 'fivepk_table_name_id', 'enter_score', 'leave_score', 'enter_year', 'enter_month', 'enter_day', 'enter_time', 'leave_time', 'reservation_cost'], 'integer'],
            [['nick_name', 'login_ip'], 'string', 'max' => 20],
            [['seo_machine_id'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                   => 'ID',
            'account_id'           => 'accountId',
            'nick_name'            => '昵称',
            'game_type'            => '游戏类型1-火凤凰',
            'machine_auto_id'      => '机台id',
            'seo_machine_id'       => '机台号',
            'fivepk_table_name_id' => 'Fivepk Table Name ID',
            'enter_score'          => '进入机台分数',
            'leave_score'          => '离开机台分数',
            'enter_year'           => '年',
            'enter_month'          => '月',
            'enter_day'            => '日',
            'enter_time'           => '进入机台时间',
            'leave_time'           => '离开机台时间',
            'reservation_cost'     => '留机消耗',
            'login_ip'             => '登录ip',
        ];
    }

    /**
     * 关联出奖记录
     * @return \yii\db\ActiveQuery
     */
    public function getPrize()
    {
        $game_list = Yii::$app->cache->get('game_list');
        if (empty($game_list)) {
            $game = ArrayHelper::map(DataGameListInfo::find()->filterWhere(['>', 'game_number', 0])->andFilterWhere(['game_switch' => 0])->orderBy('game_index ASC')->all(),
                'game_number',
                'game_name');
            Yii::$app->cache->set('game_list', $game, 60 * 20);
        } else {
            $game = Yii::$app->cache->get('game_list');
        }
        //$game = DataGameListInfo::find()->filterWhere(['game_number'=>$this->game_type])->one();

        return $this->hasOne(\Yii::$app->params[$game[$this->game_type]][1], ['fivepk_path_id' => 'id']);
    }

    /**
     * 关联用户
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(FivepkAccount::className(), ['account_id' => 'account_id'])->select($this->FivepkAccount->BaseColumn);
    }

    /**
     *  关联留机消耗
     * @return \yii\db\ActiveQuery
     */
    public function getDataReservation()
    {
        return $this->hasOne(DataReservation::className(), ['cost' => 'reservation_cost']);
    }

    public function getAccessPoint()
    {
        return $this->hasMany(FivepkAccessPinots::className(), ['fivepk_path_id' => 'id']);

//        $point = 0;
//        $models = FivepkAccessPinots::find()->filterWhere(['fivepk_path_id'=>$this->id])->all();
//        foreach ($models as $model){
//            $point += $model->up_score-$model->on_score;
//        }
//        return $point;
    }

    public function getAccessPointSum()
    {
        $access_point = $this->accessPoint;
        $point        = 0;
        foreach ($access_point as $model) {
            $point += $model->up_score - $model->on_score;
        }
        return $point;
    }

    public function page($where, $pageNo, $pageSize)
    {
        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 10000 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo - 1) * $pageSize;
        return self::find()->where($where)->orderBy('account_id desc')->offset($offset)->limit($limit)->asArray()->all();
    }

    /**
     *  获取最大条数
     */
    public function count($where)
    {
        return self::find()->where($where)->count();
    }

    /**
     * 分页获取上机轨迹  分页
     * @param $params
     * @return array
     */
    public function EnterMachinePage($params)
    {
        $pageNo           = $params['pageNo'];
        $pageSize         = $params['pageSize'];
        $promoCodes       = $params['promoCodes'];
        $machine          = $params['machine'];
        $stime            = $params['stime'];
        $etime            = $params['etime'];
        $accountId        = $params['accountId'];
        $account          = $params['account'];
        $roomSeoMachineId = $params['roomSeoMachineId'];
        $gameType         = $params['gameType'];
        $pageNo           = $pageNo < 1 ? 1 : $pageNo;
        $pageSize         = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit            = $pageSize;
        $offset           = ($pageNo - 1) * $pageSize;


        $query = self::find()->joinWith('account')->filterWhere(['in', 'fivepk_account.seoid', $promoCodes]);
        $query->andFilterWhere(['between', 'enter_time', strtotime($stime) * 1000, strtotime($etime) * 1000]);
        if (!empty($machine)) {
            $query->andFilterWhere(['like', 'seo_machine_id', $machine]);
        }
        if (!empty($account)) {
            $query->andFilterWhere(['like', 'fivepk_account.name', $account]);
        }

        if (!empty($accountId)) {
            $query->andFilterWhere(['fivepk_path.account_id' => $accountId]);
        }

        if (!empty($roomSeoMachineId)) {
            $query->innerJoin('data_room_info_list', 'data_room_info_list.id=fivepk_path.room_info_list_id and data_room_info_list.seo_machine_id=' . "'$roomSeoMachineId'");
        }

        if (!empty($gameType)) {
            $query->andFilterWhere(['fivepk_path.game_type' => $gameType]);
        }

        $query->orderBy('id DESC');
        return $query->offset($offset)->limit($limit)->asArray()->all();
    }


    /**
     * 分页获取上机轨迹  总数
     * @param $params
     * @return array
     */
    public function EnterMachineCount($params)
    {
        $promoCodes       = $params['promoCodes'];
        $machine          = $params['machine'];
        $stime            = $params['stime'];
        $etime            = $params['etime'];
        $accountId        = $params['accountId'];
        $account          = $params['account'];
        $roomSeoMachineId = $params['roomSeoMachineId'];

        $query = self::find()->joinWith('account')->filterWhere(['in', 'fivepk_account.seoid', $promoCodes]);
        $query->andFilterWhere(['between', 'enter_time', strtotime($stime) * 1000, strtotime($etime) * 1000]);
        if (!empty($machine)) {
            $query->andFilterWhere(['like', 'seo_machine_id', $machine]);
        }
        if (!empty($account)) {
            $query->andFilterWhere(['like', 'fivepk_account.name', $account]);
        }
        if (!empty($accountId)) {
            $query->andFilterWhere(['fivepk_path.account_id' => $accountId]);
        }

        if (!empty($roomSeoMachineId)) {
            $query->innerJoin('data_room_info_list', 'data_room_info_list.id=fivepk_path.room_info_list_id and data_room_info_list.seo_machine_id=' . "'$roomSeoMachineId'");
        }

        return $query->count();
    }


    /**
     * 分页获取留机轨迹  分页
     * @param $params
     * @return array
     */
    public function LeaveMachinePage($params)
    {
        $pageNo     = $params['pageNo'];
        $pageSize   = $params['pageSize'];
        $promoCodes = $params['promoCodes'];
        $accountId  = $params['accountId'];
        $account    = $params['account'];
        $machine    = $params['machine'];
        $stime      = $params['stime'];
        $etime      = $params['etime'];
        $pageNo     = $pageNo < 1 ? 1 : $pageNo;
        $pageSize   = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit      = $pageSize;
        $offset     = ($pageNo - 1) * $pageSize;

        $query = self::find()->joinWith('account')->joinWith('dataReservation')->where(['>', 'reservation_cost', 0]);
        $query->andWhere(['in', 'fivepk_account.seoid', $promoCodes]);
        $query->andWhere(['between', 'leave_time', strtotime($stime) * 1000, strtotime($etime) * 1000]);
        if (!empty($accountId)) {
            $query->andFilterWhere(['fivepk_path.account_id' => $accountId]);
        }
        if (!empty($account)) {
            $query->andFilterWhere(['like', 'fivepk_account.name', $account]);
        }
        if (!empty($machine)) {
            $query->andWhere(['like', 'seo_machine_id', $machine]);
        }
        $query->orderBy('leave_time DESC');
        return $query->offset($offset)->limit($limit)->asArray()->all();
    }


    /**
     * 分页获取留机轨迹  总数
     * @param $params
     * @return array
     */
    public function LeaveMachineCount($params)
    {
        $promoCodes = $params['promoCodes'];
        $accountId  = $params['accountId'];
        $account    = $params['account'];
        $machine    = $params['machine'];
        $stime      = $params['stime'];
        $etime      = $params['etime'];

        $query = self::find()->joinWith('account')->where(['>', 'reservation_cost', 0]);
        $query->andWhere(['in', 'fivepk_account.seoid', $promoCodes]);
        $query->andWhere(['between', 'leave_time', strtotime($stime) * 1000, strtotime($etime) * 1000]);
        if (!empty($accountId)) {
            $query->andFilterWhere(['fivepk_path.account_id' => $accountId]);
        }
        if (!empty($account)) {
            $query->andFilterWhere(['like', 'fivepk_account.name', $account]);
        }
        if (!empty($machine)) {
            $query->andWhere(['like', 'seo_machine_id', $machine]);
        }
        return $query->count();
    }

    /**
     * 根据accountid获取数据
     * @param $accountIds 数组
     * @return array
     */
    public function findByAccountIds($accountIds)
    {
        return self::find()->where(['in', 'account_id', $accountIds])->asArray()->all();
    }

    /**
     *  获取 没有计算 后台统计的path
     */
    public function GetNotInitPath()
    {
        $time = (time() - 30) * 1000;
        return self::find()->where('(common_json is null or common_json = "") and leave_time <> "" and leave_time< :time', array(':time' => $time))->all();
    }

}
