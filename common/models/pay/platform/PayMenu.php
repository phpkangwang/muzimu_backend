<?php
namespace common\models\pay\platform;

use backend\models\BaseModel;
use Yii;

class PayMenu extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pay_menu';
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
            'id'     => 'ID',
            'name'   => '银行名称',
            'status' => '状态',
        ];
    }

    /**
     *  获取列表
     * @return array
     */
    public function tableList()
    {
        return self::find()->asArray()->all();
    }

    /**
     *  获取列表
     * @return array
     */
    public function tableListSelect()
    {
        return self::find()->asArray()->where('status=1')->all();
    }

}
