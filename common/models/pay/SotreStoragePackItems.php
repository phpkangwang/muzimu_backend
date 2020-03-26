<?php
namespace common\models\pay;

use Yii;

/**
 * This is the model class for table "StoreItemListData".
 *
 */
class SotreStoragePackItems extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sotre_storage_pack_items';
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
            'account_id'      => '背包拥有者',
            'item_type'       => '道具code',
            'item_count'      => '道具个数',
            'update_time'     => '修改时间',
            'create_time'     => '创建时间',
        ];
    }

    public function findByIds($accountIds)
    {
        return self::find()->where(['account_id' => $accountIds])->asArray()->all();
    }

}
