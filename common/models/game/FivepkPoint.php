<?php

namespace common\models\game;

use Yii;

/**
 * This is the model class for table "fivepk_point".
 *
 * @property integer $id
 * @property integer $account_id
 * @property string $operator
 * @property integer $before_operate
 * @property integer $up_coin
 * @property integer $down_coin
 * @property integer $after_operate
 * @property string $operate_time
 * @property string $belong_seoid
 */
class FivepkPoint extends \backend\models\BaseModel
{

    const OPERATOR_TYPE_USER = 1;//1手动操作
    const OPERATOR_TYPE_PAY = 2;//2充值
    const OPERATOR_TYPE_TURN = 3;//3转出中
    const OPERATOR_TYPE_TRUE = 4;//4转出成功
    const OPERATOR_TYPE_RETURN = 5;//5转出退还

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_point';
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
            [['account_id', 'before_operate', 'up_coin', 'down_coin', 'after_operate', 'id'], 'integer'],
            [['operate_time'], 'safe'],
            [['operator'], 'string', 'max' => 50],
            [['belong_seoid'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'             => 'ID',
            'account_id'     => 'Account ID',
            'operator'       => '操作人',
            'before_operate' => '操作前',
            'up_coin'        => '上钻数',
            'down_coin'      => '下钻数',
            'after_operate'  => '操作后',
            'operate_time'   => 'Operate Time',
            'belong_seoid'   => '所属代理商',
        ];
    }

    /**
     * 关联玩家
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(FivepkAccount::className(), ['account_id' => 'account_id']);
    }

    /**
     * 关联玩家信息说
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerInfo()
    {
        return $this->hasOne(FivepkPlayerInfo::className(), ['account_id' => 'account_id']);
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
        $offset   = ($pageNo - 1) * $pageSize;
        return self::find()->joinWith('account')->joinWith('playerInfo')->where($where)->offset($offset)->limit($limit)->orderBy('fivepk_point.id DESC')->asArray()->all();
    }

    /**
     *  获取最大条数
     */
    public function UserDiamondRecordCount($where)
    {
        return self::find()->joinWith('account')->joinWith('playerInfo')->where($where)->count();
    }

    /**
     * 修改状态
     * @param $id
     * @param $status
     * @param string $operator
     * @return bool
     */

    public function upStatus($id, $status, $operator = '')
    {
        $obj = $this->findOneByField('id', $id);
        if (empty($obj)) {
            return false;
        }
        return $obj->add(['operator_type' => $status, 'operator' => $operator]);
    }


}
