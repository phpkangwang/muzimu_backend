<?php
namespace common\models;

use backend\models\BaseModel;
use Yii;

/**
 * This is the model class for table "attention".
 *
 * @property integer $id
 * @property integer $account_id
 * @property string $nick_name
 * @property integer $reward
 * @property integer $time
 * @property string $operator
 */
class Attention extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'attention';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account_id', 'nick_name','reward'], 'required','message'=>'{attribute}不能为空'],
            [['id', 'account_id', 'time'], 'integer'],
            [['reward'],'integer','min'=>0],
            [['nick_name', 'operator'], 'string', 'max' => 255],
            [['account_id'], 'unique','message'=>'该{attribute}已存在']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account_id' => '用户ID',
            'nick_name' => '昵称',
            'reward' => '关注奖励',
            'time' => '时间',
            'operator' => '操作人',
        ];
    }

    public static function getCounts($query)
    {
        $result = $query->select('count(id) as total_count,sum(reward) as total_rewards')->asArray()->all();
        return $result;
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

    //关注总人数
    public function getAccountNum($where)
    {
        return self::find()->where($where)->count();
    }

    //关注总奖励
    public function getRewardSum($where)
    {
        $reward = self::find()->where($where)->sum('reward');
        return $reward=="" ? 0 : $reward;
    }

    //根据用户id获取信息
    public function findByAccountId($accountId)
    {
        return self::find()->where('account_id = :account_id',array('account_id'=>$accountId))->asArray()->one();
    }
 
}
