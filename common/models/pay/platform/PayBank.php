<?php
namespace common\models\pay\platform;

use backend\models\BaseModel;
use Yii;

class PayBank extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pay_bank';
    }

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
            'id'   => 'ID',
            'name' => '银行名称',
            'icon' => 'icon地址',
        ];
    }

    /**
     *  获取列表
     * @return array
     */
    public function tableList()
    {
        return self::find()->orderBy('id asc')->asArray()->all();
    }

}
