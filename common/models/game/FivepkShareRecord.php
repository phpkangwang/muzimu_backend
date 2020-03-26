<?php
namespace common\models\game;

use backend\models\Tool;
use Yii;

/**
 * This is the model class for table "fivepk_share_record".
 *
 * @property integer $id
 * @property string $account
 * @property integer $type
 * @property integer $bonus
 * @property integer $share_fb_zone
 * @property integer $share_fb_friend
 * @property string $date
 */
class FivepkShareRecord extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_share_record';
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
            [['account', 'type', 'bonus', 'share_fb_zone', 'share_fb_friend'], 'integer'],
            [['date'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account' => 'Account',
            'type' => '分享的类型0-朋友圈1-好友',
            'bonus' => '奖励分',
            'share_fb_zone' => '已分享动态的次数',
            'share_fb_friend' => '分享朋友的次数',
            'date' => '推广奖励到的时间',
        ];
    }

    public function getPlayerInfo()
    {
        return $this->hasOne(FivepkPlayerInfo::className(),['account_id'=>'account']);
    }

    /**
     * 分页
     * @return array
     */
    public function page($pageNo, $pageSize, $where)
    {
        $pageArr = Tool::page($pageNo,$pageSize);
        $offset  = $pageArr['offset'];
        $limit   = $pageArr['limit'];
        $sql = "
                select sr.*,account.seoid,info.nick_name from fivepk_share_record as sr
                left join fivepk_account as account on sr.account_id  = account.account_id
                left join fivepk_player_info as info on sr.account_id = info.account_id
                where {$where}
                order by sr.`create_time` desc
                limit {$limit}
                OFFSET {$offset}
        ";
        return Yii::$app->game_db->createCommand($sql)->queryAll();
    }

    /**
     * 分页数量
     * @return array
     */
    public function pageCount( $where )
    {
        $sql = "
                select sr.id from fivepk_share_record as sr
                left join fivepk_account as account on sr.account_id = account.account_id
                where {$where}
        ";
        $obj = Yii::$app->game_db->createCommand($sql)->queryAll();
        return count($obj);
    }

    /**
     *  统计
     * @param $where
     * @return array|\yii\db\ActiveRecord[]
     */
    public function statistic( $where )
    {
        $sql = "
                select sr.* from fivepk_share_record as sr
                left join fivepk_account as account on sr.account_id = account.account_id
                where {$where}
        ";
        return Yii::$app->game_db->createCommand($sql)->queryAll();

    }

 
}
