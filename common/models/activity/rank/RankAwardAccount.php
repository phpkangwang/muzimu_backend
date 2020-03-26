<?php
namespace common\models\activity\rank;

use backend\models\BaseModel;
use Yii;

class RankAwardAccount extends BaseModel
{

    /**
     * 表名
     */
    public static function tableName()
    {
        return 'rank_award_account';
    }

    /**
     *  设置数据库链接
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
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
            'ranking_type' => '1日 2周 3月',
            'account_id' => '用户id',
            'award_type' => '奖励类型',
            'award_num' => '奖励数量',
            'create_time' => '奖励时间',
            'order' => '排名',
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
        return self::find()->offset($offset)->where($where)->orderBy('create_time desc, order asc')->limit($limit)->asArray()->all();
    }

    /**
     *  获取最大条数
     */
    public function pageCount($where)
    {
        return self::find()->where($where)->count();
    }
}
