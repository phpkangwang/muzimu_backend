<?php

namespace common\models\activity\recharge;

use backend\models\BaseModel;
use backend\models\MyException;
use Yii;

class RechargeGear extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity_recharge_gear_price_data';
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
            [['activity_type', 'recharge_gear', 'recharge_price'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'             => 'ID',
            'activity_type'  => '1单个充值;2累积充值',
            'recharge_gear'  => '档位名称',
            'recharge_price' => '价格'
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
