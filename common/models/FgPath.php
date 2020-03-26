<?php

namespace common\models;

use backend\models\Tool;
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
class FgPath extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fg_path';
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
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'account_id'  => 'accountId',
            'nick_name'   => '昵称',
            'game_type'   => '游戏类型',
            'enter_score' => '进入机台分数',
            'leave_score' => '离开机台分数',
            'enter_time'  => '进入机台时间',
            'leave_time'  => '离开机台时间',
            'enter_coin'  => '进入机台钻石数',
            'leave_coin'  => '离开机台钻石数',
            'login_ip'    => '登录ip',
        ];
    }

    /**
     * 关联用户
     * @return \yii\db\ActiveQuery
     */
    public function getGame()
    {
        return $this->hasOne(DataGameListInfo::className(), ['game_number' => 'game_type'])->select('game_number,game_name');
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
     * 分页获取上机轨迹  分页
     * @param $params
     * @return array
     */
    public function EnterMachinePage($params)
    {
        $accountId  = $params['accountId'];
        $account    = $params['account'];
        $gameType   = $params['gameType'];
        $promoCodes = $params['promoCodes'];
        $stime      = strtotime($params['stime']) * 1000;
        $etime      = strtotime($params['etime']) * 1000;
        $pageNo     = $params['pageNo'];
        $pageSize   = $params['pageSize'];
        $pageObj    = Tool::page($pageNo, $pageSize);
        $limit      = $pageObj['limit'];
        $offset     = $pageObj['offset'];

        $query = self::find()
            ->joinWith('game')
            ->joinWith('account')
            ->filterWhere(['in', 'fivepk_account.seoid', $promoCodes])
            ->Where(' ( (leave_time between :stime and :etime) or leave_time = 0)',array(':stime'=>$stime,':etime'=>$etime));

        if (!empty($accountId)) {
            $query->andFilterWhere(['fg_path.account_id' => $accountId]);
        }

        if (!empty($account)) {
            $query->andFilterWhere(['fg_path.name' => $account]);
        }

        if (!empty($gameType)) {
            $query->andFilterWhere(['fg_path.game_type' => $gameType]);
        }

        return $query->orderBy('id DESC')->offset($offset)->limit($limit)->asArray()->all();
    }

    /**
     * 分页获取上机轨迹  总数
     * @param $params
     * @return array
     */
    public function EnterMachineCount($params)
    {
        $accountId  = $params['accountId'];
        $account    = $params['account'];
        $gameType   = $params['gameType'];
        $promoCodes = $params['promoCodes'];
        $stime      = strtotime($params['stime']) * 1000;
        $etime      = strtotime($params['etime']) * 1000;

        $query = self::find()
            ->joinWith('account')
            ->filterWhere(['in', 'fivepk_account.seoid', $promoCodes])
            ->Where(' ( (leave_time between :stime and :etime) or leave_time = 0)',array(':stime'=>$stime,':etime'=>$etime));

        if (!empty($accountId)) {
            $query->andFilterWhere(['fg_path.account_id' => $accountId]);
        }

        if (!empty($account)) {
            $query->andFilterWhere(['fg_path.name' => $account]);
        }

        if (!empty($gameType)) {
            $query->andFilterWhere(['fg_path.game_type' => $gameType]);
        }

        return $query->count();
    }


    /**
     *  分页
     * @param $param
     * @return array|\yii\db\ActiveRecord[]
     */
    public function RecordPlayerPage($param)
    {
        $accountId = $param['accountId'];
        $gameName  = $param['gameName'];
        $gameType  = $param['gameType'];
        $pageNo    = $param['pageNo'];
        $pageSize  = $param['pageSize'];
        $stime     = $param['stime'] * 1000;
        $etime     = $param['etime'] * 1000;
        $pageObj   = Tool::page($pageNo, $pageSize);

        $query = self::find()
            ->select(   'fg_path.*,( (sum(enter_score) - sum(leave_score)) / 100 + (sum(enter_coin) - sum(leave_coin)) ) as profit'   )
            ->joinWith('game')->andWhere('leave_time >= :stime and leave_time <= :etime',array(':stime'=>$stime,':etime'=>$etime));
        if ($accountId != "") {
            $query->andWhere('account_id = :account_id', array(":account_id" => $accountId));
        }

        if ($gameName != "") {
            $query->andWhere('data_game_list_info.game_name = :game_name', array(":game_name" => $gameName));
        }

        if ($gameType != "") {
            $query->andWhere('game_type = :game_type', array(":game_type" => $gameType));
        }

        $data =  $query->groupBy('account_id,game_type')->offset($pageObj['offset'])->limit($pageObj['limit'])->orderBy('leave_time desc')->asArray()->all();
        foreach ($data as $key => $val){
            $data[$key]['profit'] = floatval($val['profit']);
        }
        return $data;
    }

    /**
     *  分页
     * @param $param
     * @return array|\yii\db\ActiveRecord[]
     */
    public function RecordPlayerPageSum($param)
    {
        $accountId = $param['accountId'];
        $gameName  = $param['gameName'];
        $gameType  = $param['gameType'];
        $stime     = $param['stime'] * 1000;
        $etime     = $param['etime'] * 1000;

        $query = self::find()
            ->select(   'game_type,( (sum(enter_score) - sum(leave_score)) / 100 + (sum(enter_coin) - sum(leave_coin)) ) as profit'   )
            ->joinWith('game')->andWhere('leave_time >= :stime and leave_time <= :etime',array(':stime'=>$stime,':etime'=>$etime));

        if ($accountId != "") {
            $query->andWhere('account_id = :account_id', array(":account_id" => $accountId));
        }

        if ($gameName != "") {
            $query->andWhere('data_game_list_info.game_name = :game_name', array(":game_name" => $gameName));
        }

        if ($gameType != "") {
            $query->andWhere('game_type = :game_type', array(":game_type" => $gameType));
        }
        $data = $query->asArray()->one();
        if( empty($data['profit']) ){
            return 0;
        }else{
            $profit =  floatval($data['profit']);
            return $profit;
        }
    }



    /**
     *  分页
     * @param $param
     * @return array|\yii\db\ActiveRecord[]
     */
    public function RecordGamePage($param)
    {
        $pageNo    = $param['pageNo'];
        $pageSize  = $param['pageSize'];
        $stime     = $param['stime'] * 1000;
        $etime     = $param['etime'] * 1000;
        $pageObj   = Tool::page($pageNo, $pageSize);

        $query = self::find()
            ->select(   'fg_path.*,( (sum(enter_score) - sum(leave_score)) / 100 + (sum(enter_coin) - sum(leave_coin)) ) as profit'   )
            ->joinWith('game')->andWhere('leave_time >= :stime and leave_time <= :etime',array(':stime'=>$stime,':etime'=>$etime));

        $data =  $query->groupBy('game_type')->offset($pageObj['offset'])->limit($pageObj['limit'])->orderBy('leave_time desc')->asArray()->all();
        foreach ($data as $key => $val){
            $data[$key]['profit'] = floatval($val['profit']);
        }
        return $data;
    }

    /**
     *  分页
     * @param $param
     * @return array|\yii\db\ActiveRecord[]
     */
    public function RecordGamePageSum($param)
    {
        $stime     = $param['stime'] * 1000;
        $etime     = $param['etime'] * 1000;

        $query = self::find()
            ->select(   'game_type,( (sum(enter_score) - sum(leave_score)) / 100 + (sum(enter_coin) - sum(leave_coin)) ) as profit'   )
            ->joinWith('game')->andWhere('leave_time >= :stime and leave_time <= :etime ',array(':stime'=>$stime,':etime'=>$etime));

        $data = $query->asArray()->one();
        if( empty($data['profit']) ){
            return 0;
        }else{
            $profit =  floatval($data['profit']);
            return $profit;
        }
    }

}
