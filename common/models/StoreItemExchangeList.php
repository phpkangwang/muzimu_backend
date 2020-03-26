<?php
namespace common\models;

use backend\models\BaseModel;
use backend\models\MyException;
use Yii;


class StoreItemExchangeList extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'store_item_exchange_list_data';
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
//            [['prize', 'max_num', 'lost_num', 'exchange_type', 'award_num'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'item_type'    => '道具类型',
            'item_name'    => '道具名称',
            'description'  => '描述',
            'prize'        => '价格',
            'max_num'      => '最大数量',
            'lost_num'     => '剩余数量',
            'exchange_type'=> '1金币 2话费',
            'award_num'    => '奖品数量',
            'updated_time' => '修改时间',
            'created_time' => '创建时间',
        ];
    }

    /**
     *  获取列表
     * @return array
     */
    public function tableList()
    {
        return self::find()->orderBy('id ASC')->asArray()->all();
    }

    public function del($id)
    {
        return self::deleteAll("id=:id",[':id'=>$id]);
    }
 
}
