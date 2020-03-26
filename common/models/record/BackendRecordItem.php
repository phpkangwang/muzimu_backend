<?php
namespace common\models\record;

use backend\models\BaseModel;
use Yii;

class BackendRecordItem extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'backend_record_item';
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
            'id'               => 'ID',
            'account_id'       => '用户ID',
            'item_type'        => '商品类型',// store_item_list_data 的 item_type
            'num'              => '获取数量',
            'game_type'        => '游戏类型',
            'room_index'       => '房间等级',//1体验场 2初级场
            'machine_auto_id'  => '机台autoid',
            'create_time'      => '修改时间',
        ];
    }


    public function deleteByTime($stime, $etime)
    {
        return self::deleteAll("create_time BETWEEN :stime and :etime",[':stime'=>$stime, ':etime'=>$etime]);
    }
}
