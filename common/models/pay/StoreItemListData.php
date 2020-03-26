<?php
namespace common\models\pay;

use Yii;

/**
 * This is the model class for table "StoreItemListData".
 *
 */
class StoreItemListData extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'store_item_list_data';
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
            'id'              => 'ID',
            'item_type'      => '道具类型',
            'name'       => '名称',
            'description'      => '描述',
            'max_num'     => '最大叠加数量',
            'sort'     => '排序',
            'updated_time'     => '修改时间',
            'created_time'     => '创建时间',
        ];
    }



}
