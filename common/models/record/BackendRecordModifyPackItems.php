<?php
namespace common\models\record;

use backend\models\BaseModel;
use common\models\game\FivepkAccount;
use common\models\game\FivepkPlayerInfo;
use Yii;

class BackendRecordModifyPackItems extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'backend_record_modify_pack_items';
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
            'id' => 'ID',
            'account_id' => 'Account ID',
            'operator' => '操作人',
            'before_operate' => '操作前',
            'up_coin' => '增加数',
            'down_coin' => '减少数',
            'after_operate' => '操作后',
            'operate_time' => '操作时间',
            'belong_seoid' => '所属代理商',
            'item_type' => '商品类型',// store_item_list_data 的 item_type
        ];
    }


    /**
     * 关联玩家
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(FivepkAccount::className(),['account_id'=>'account_id']);
    }

    /**
     * 关联玩家信息说
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerInfo()
    {
        return $this->hasOne(FivepkPlayerInfo::className(),['account_id'=>'account_id']);
    }

    /**
     * 分页
     * @return array
     */
    public function UserDiamondRecordPage($pageNo, $pageSize, $where)
    {
        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo-1)*$pageSize;
        return self::find()->joinWith('account')->joinWith('playerInfo')->where($where)->offset($offset)->limit($limit)->orderBy('backend_record_modify_pack_items.id DESC')->asArray()->all();
    }

    /**
     *  获取最大条数
     */
    public function UserDiamondRecordCount($where)
    {
        return self::find()->joinWith('account')->joinWith('playerInfo')->where($where)->count();
    }

}
