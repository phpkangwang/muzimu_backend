<?php
namespace common\models;

use Yii;

class OnlinePlayerTotalCountType extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'online_player_total_count_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'created_at'=>'记录时间',
            'pay_type' => '1-苹果2-谷歌3-OPPO',
            'sum_recharge_money' => '当日充值总金额',
        ];
    }


 
}
