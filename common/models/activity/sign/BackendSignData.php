<?php
namespace common\models\activity\sign;

use backend\models\BaseModel;
use backend\models\Tool;
use Yii;

class BackendSignData extends BaseModel
{

    /**
     * 表名
     */
    public static function tableName()
    {
        return 'backend_sign_data';
    }

    /**
     *  设置数据库链接
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
        return [];

    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增ID',
            'account_id' => '用户id',
            'seoid' => '推广号',
            'item_type' => '道具类型0金币',
            'nick_name' => '昵称',
            'num' => '道具数量',
            'day' => '第几天',
            'time' => '领取时间',
        ];
    }

}
