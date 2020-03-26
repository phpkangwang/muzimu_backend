<?php
namespace common\models;

use common\models\game\FivepkAccount;
use common\models\game\FivepkPlayerInfo;
use Yii;

/**
 * This is the model class for table "fivepk_access_points".
 *
 * @property integer $id
 * @property string $fivepk_path_id
 * @property string $account_id
 * @property string $seoid
 * @property integer $on_score
 * @property integer $on_coin
 * @property integer $up_score
 * @property integer $up_coin
 * @property string $last_time
 */
class FivepkAccessPinots extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_access_points';
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
            [['fivepk_path_id', 'account_id', 'on_score', 'on_coin', 'up_score', 'up_coin', 'last_time'], 'integer'],
            [['seoid'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fivepk_path_id' => 'Fivepk Path ID',
            'account_id' => 'Account ID',
            'seoid' => '代理商',
            'on_score' => '兑换前分数',
            'on_coin' => '兑换前钻数',
            'up_score' => '兑换后分数',
            'up_coin' => '兑换后钻数',
            'last_time' => '兑换时间',
        ];
    }

    /**
     * @desc 关联玩家表
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(FivepkAccount::className(),['account_id'=>'account_id'])->select($this->FivepkAccount->BaseColumn);
    }

    /**
     * @desc 关联玩家详细信息
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerInfo()
    {
        return $this->hasOne(FivepkPlayerInfo::className(),['account_id'=>'account_id'])->select($this->FivepkPlayerInfo->BaseColumn);
    }

    /**
     *   玩家兑换记录分页查询  分页
     */
    public function ExchangePage($params)
    {
        $pageNo     = $params['pageNo'];
        $pageSize   = $params['pageSize'];
        $stime      = $params['stime'];
        $etime      = $params['etime'];
        $promoCodes = $params['promoCodes'];
        $account    = $params['account'];
        $accountId  = $params['accountId'];

        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo-1)*$pageSize;
        $query = self::find()->joinWith('account')->joinWith('playerInfo')->where(['in','fivepk_account.seoid',$promoCodes]);
        if( !empty($account) ){
            $query->andWhere(['like', 'fivepk_account.name', $account]);
        }
        if( !empty($accountId) ){
            $query->andWhere(['fivepk_access_points.account_id' => $accountId]);
        }


        if( !empty($stime) && !empty($etime) ){
            $query->andWhere(['between', 'last_time', strtotime($stime) * 1000, strtotime($etime) * 1000]);
        }
        $query = $query->orderBy('id DESC');
        return $query->offset($offset)->limit($limit)->asArray()->all();
    }

    /**
     *   玩家兑换记录分页查询  总数量
     */
    public function ExchangeCount($params)
    {
        $stime      = $params['stime'];
        $etime      = $params['etime'];
        $promoCodes = $params['promoCodes'];
        $account    = $params['account'];
        $accountId  = $params['accountId'];

        $query = self::find()->joinWith('account')->joinWith('playerInfo')->where(['in','fivepk_account.seoid',$promoCodes]);
        if( !empty($account) ){
            $query->andWhere(['like', 'fivepk_account.name', $account]);
        }

        if( !empty($accountId) ){
            $query->andWhere(['fivepk_access_points.account_id' => $accountId]);
        }
        if( !empty($stime) && !empty($etime) ){
            $query->andWhere(['between', 'last_time', strtotime($stime) * 1000, strtotime($etime) * 1000]);
        }
        return $query->count();
    }

    public function findAccessPointSum($fivepkPathId)
    {
        $point = 0;
        $datas = self::find()->where('fivepk_path_id = :fivepk_path_id', array(":fivepk_path_id" => $fivepkPathId))->asArray()->all();
        foreach ($datas as $data){
            $point += $data['up_score']-$data['on_score'];
        }
        return $point;
    }

    public function findAccessPointSumSl($fivepkPathId)
    {
        $point   = 0;
        $pointSl = 0;
        $datas   = self::find()->where('fivepk_path_id = :fivepk_path_id', array(":fivepk_path_id" => $fivepkPathId))->asArray()->all();
        foreach ($datas as $data) {
            $point   += $data['up_score'] - $data['on_score'];
            $pointSl += $data['up_score_sl'] - $data['on_score_sl'];
        }
        return ['point' => $point, 'pointSl' => $pointSl];
    }


}
