<?php
namespace common\models\game;

use Yii;

/**
 * This is the model class for table "fivepk_gived_gift".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $giver_id
 * @property string $giver_nickname
 * @property integer $give_diamond_before
 * @property integer $give_diamond_after
 * @property integer $diamond
 * @property integer $receiver_id
 * @property string $receiver_nickname
 * @property integer $receiver_diamond_before
 * @property integer $receiver_diamond_after
 * @property string $create_time
 * @property string $update_time
 * @property integer $status
 */
class FivepkGivedGift extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_gived_gift';
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
            [['id'], 'required'],
            [['id', 'order_id', 'giver_id', 'give_diamond_before', 'give_diamond_after', 'diamond', 'receiver_id', 'receiver_diamond_before', 'receiver_diamond_after', 'status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['giver_nickname', 'receiver_nickname'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'order_id' => '赠送订单号',
            'giver_id' => '赠送人ID',
            'giver_nickname' => '赠送人昵称',
            'give_diamond_before' => '赠送人赠送前钻石数',
            'give_diamond_after' => '赠送人赠送红钻石数',
            'diamond' => '赠送钻石数',
            'receiver_id' => '接收人ID',
            'receiver_nickname' => '接收人昵称',
            'receiver_diamond_before' => '接收人接收前钻石数',
            'receiver_diamond_after' => '接收人接收后钻石数',
            'create_time' => '赠送时间',
            'update_time' => '接收时间',
            'status' => '状态0-未接收，1-已接收',
        ];
    }

    /**
     * 分页
     * @return array
     */
    public function page($pageNo, $pageSize, $where)
    {
        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo-1)*$pageSize;
        return self::find()->where($where)->offset($offset)->orderBy('id desc')->limit($limit)->asArray()->all();
    }

    /**
     * 分页数量
     * @return array
     */
    public function pageCount( $where )
    {
        return self::find()->where($where)->count();
    }
 
}
