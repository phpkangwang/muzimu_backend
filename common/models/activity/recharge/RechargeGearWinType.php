<?php

namespace common\models\activity\recharge;

use backend\models\BaseModel;
use backend\models\MyException;
use Yii;

class RechargeGearWinType extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity_recharge_gear_get_win_type_data';
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
            [['game_type', 'recharge_gear', 'recharge_gear_get_win_type_rate', 'rate_of_player_score'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                              => 'ID',
            'game_type'                       => '游戏类型',
            'recharge_gear'                   => '档位名称',
            'recharge_gear_get_win_type_rate' => 'data_prize_type的rate字段json',
            'rate_of_player_score'            => '放奖的百分比分数'
        ];
    }

    /**
     *  获取列表
     * @return array
     */
    public function tableList()
    {
        return self::find()->orderBy('recharge_gear ASC')->asArray()->all();
    }

    /**
     * 根据gameType获取所有
     * @param $gameType
     * @return array|\yii\db\ActiveRecord[]
     */
    public function findByGameType($gameType)
    {
        return self::find()->where('game_type = :game_type',array(':game_type'=>$gameType))->orderBy('recharge_gear ASC')->asArray()->all();
    }

    /**
     * 根据gameType $rechargeGear获取单条数据
     * @param $gameType
     * @param $rechargeGear
     * @return array|\yii\db\ActiveRecord[]
     */
    public function findByGameTypeRechargeGear($gameType,$rechargeGear)
    {
        return self::find()->where('game_type = :game_type and recharge_gear = :recharge_gear',array(':game_type'=>$gameType,':recharge_gear'=>$rechargeGear))->one();
    }

    /**
     * 添加
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        try {
            foreach ($data as $key => $val) {
                $this->$key = $val;
            }
            if ($this->validate() && $this->save()) {
                return $this->attributes;
            } else {
                throw new MyException(json_encode($this->getErrors()));
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    public function del($id)
    {
        return self::deleteAll("id=:id", [':id' => $id]);
    }

}
