<?php

namespace common\models\game\byu;

use backend\models\remoteInterface\remoteInterface;
use common\models\game\FivepkAccount;
use common\models\game\FivepkPlayerInfo;
use Yii;

class ByuPlayerOdds extends Byu
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fish_player_info';
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

        ];
    }

    /**
     * 关联玩家信息说
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerInfo()
    {
        return $this->hasOne(FivepkPlayerInfo::className(), ['account_id' => 'account_id'])->select('account_id,nick_name');
    }

    /**
     * 关联玩家信息说
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(FivepkAccount::className(), ['account_id' => 'account_id'])->select('account_id,seoid');
    }

    /**
     * 分页获取 玩家机率 列表
     * @param $params
     * @return array|\yii\db\ActiveRecord[]
     */
    public function playerOddsPage($params)
    {
        $pageSize = $params['pageSize'];
        $popCode  = $params['popCode'];
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $lastId   = $params['lastId'] == "" ? 0 : $params['lastId'];//上次请求最后一个id，防止分页数据重复
        $query    = self::find()->where('id > :lastId', array(":lastId" => $lastId));
        if (!empty($params['accountId'])) {
            $query->andWhere('fish_player_info.account_id = :account_id', array(':account_id' => $params['accountId']));
        }
        if ( $popCode != "" ) {
            $query->andWhere('fivepk_account.seoid = :popCode', array(':popCode' => $popCode));
        }
        return $query->joinWith('playerInfo')->joinWith('account')->limit($limit)->asArray()->all();
    }

    /**
     * 根据用户idarr  批量修改数据
     * @param $accountIdArr
     * @param $postData
     * @param $type
     * @return int
     */
    public function updateByAccounts($accountIdArr, $postData, $type)
    {
        $remoteInterfaceObj = new remoteInterface();
        if ($type == 1) {
            //修改单个用户机率
            self::updateAll($postData, ['and', ['in', 'account_id', $accountIdArr]]);
            $data = array(
                'accountIds' => implode(",", $accountIdArr),
                'type'       => 1,
            );
        } else if($type == 2){
            $DefaultOddsModel = $this->getModelDefaultOdds();
            $postData = $DefaultOddsModel->findDefaultData();
            //修改所有用户
            self::updateAll($postData, ['and', ['in', 'account_id', $accountIdArr]]);
            $data = array(
                'accountIds' => implode(",", $accountIdArr),
                'type'       => 2,
            );
        }else if($type == 3){
            $DefaultOddsModel = $this->getModelDefaultOdds();
            $postData = $DefaultOddsModel->findDefaultData();
            //修改所有用户
            self::updateAll($postData);
            $data = array(
                'accountIds' => "",
                'type'       => 3,
            );
        }
        //$remoteInterfaceObj->refreshPlayerOddsByu($data);
        return true;
    }
}
